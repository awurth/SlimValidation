<?php

namespace Awurth\SlimValidation;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReflectionClass;
use ReflectionProperty;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AbstractComposite;
use Respect\Validation\Validator as RespectValidator;

/**
 * Validator.
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 */
class Validator
{
    const MODE_ASSOCIATIVE = 1;
    const MODE_INDEXED = 2;

    /**
     * The validated data.
     *
     * @var array
     */
    protected $values;

    /**
     * The default error messages for the given rules.
     *
     * @var string[]
     */
    protected $defaultMessages;

    /**
     * The list of validation errors.
     *
     * @var string[]
     */
    protected $errors;

    /**
     * Tells whether errors should be stored in an associative array
     * or in an indexed array.
     *
     * @var int
     */
    protected $errorStorageMode;

    /**
     * Constructor.
     *
     * @param int      $errorStorageMode
     * @param string[] $defaultMessages
     */
    public function __construct(int $errorStorageMode = self::MODE_ASSOCIATIVE, array $defaultMessages = [])
    {
        $this->errorStorageMode = $errorStorageMode;
        $this->defaultMessages = $defaultMessages;
        $this->errors = [];
        $this->values = [];
    }

    /**
     * Tells if there is no error.
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * Validates an array with the given rules.
     *
     * @param array  $array
     * @param array  $rules
     * @param array  $messages
     * @param string $group
     *
     * @return self
     */
    public function array(array $array, array $rules, array $messages = [], $group = null)
    {
        foreach ($rules as $key => $options) {
            $value = $array[$key] ?? null;

            $this->value($value, $key, $options, $messages, $group);
        }

        return $this;
    }

    /**
     * Validates an objects properties with the given rules.
     *
     * @param object     $object
     * @param array      $rules
     * @param array      $messages
     * @param string|int $group
     *
     * @return self
     */
    public function object(object $object, array $rules, array $messages = [], $group = null)
    {
        foreach ($rules as $property => $options) {
            $value = $this->getPropertyValue($object, $property);

            $this->value($value, $property, $options, $messages, $group);
        }

        return $this;
    }

    /**
     * Validates request parameters with the given rules.
     *
     * @param Request $request
     * @param array   $rules
     * @param array   $messages
     * @param string  $group
     *
     * @return self
     */
    public function request(Request $request, array $rules, array $messages = [], $group = null)
    {
        foreach ($rules as $param => $options) {
            $value = $this->getRequestParam($request, $param);

            $this->value($value, $param, $options, $messages, $group);
        }

        return $this;
    }

    /**
     * Validates request parameters, an array or an objects properties.
     *
     * @param Request|object|array $input
     * @param array                $rules
     * @param array                $messages
     * @param string               $group
     *
     * @return self
     */
    public function validate($input, array $rules, array $messages = [], $group = null)
    {
        if (!is_object($input) && !is_array($input)) {
            throw new InvalidArgumentException('The input must be either an object or an array');
        }

        if ($input instanceof Request) {
            return $this->request($input, $rules, $messages, $group);
        } elseif (is_array($input)) {
            return $this->array($input, $rules, $messages, $group);
        } elseif (is_object($input)) {
            return $this->object($input, $rules, $messages, $group);
        }

        return $this;
    }

    /**
     * Validates a single value with the given rules.
     *
     * @param mixed                  $value
     * @param string                 $key
     * @param RespectValidator|array $rules
     * @param array                  $messages
     * @param string                 $group
     *
     * @return self
     */
    public function value($value, $key, $rules, array $messages = [], $group = null)
    {
        try {
            if ($rules instanceof RespectValidator) {
                $rules->assert($value);
            } else {
                if (!is_array($rules) || !isset($rules['rules']) || !($rules['rules'] instanceof RespectValidator)) {
                    throw new InvalidArgumentException('Validation rules are missing');
                }

                $rules['rules']->assert($value);
            }
        } catch (NestedValidationException $e) {
            // If the 'message' key exists, set it as only message for this param
            if (is_array($rules) && isset($rules['message'])) {
                if (!is_string($rules['message'])) {
                    throw new InvalidArgumentException(sprintf('Expected custom message to be of type string, %s given', gettype($rules['message'])));
                }

                if (null !== $group) {
                    $this->errors[$group][$key] = [$rules['message']];
                } else {
                    $this->errors[$key] = [$rules['message']];
                }
            } else {
                // If the 'messages' key exists, override global messages
                $this->setMessages($e, $key, $rules, $messages, $group);
            }
        }

        $this->setValue($key, $value, $group);

        return $this;
    }

    /**
     * Gets the error count.
     *
     * @return int
     */
    public function count()
    {
        return count($this->errors);
    }

    /**
     * Adds an error for a parameter.
     *
     * @param string $param
     * @param string $message
     * @param string $group
     *
     * @return self
     */
    public function addError($param, $message, $group = null)
    {
        if (null !== $group) {
            $this->errors[$group][$param][] = $message;
        } else {
            $this->errors[$param][] = $message;
        }

        return $this;
    }

    /**
     * Gets one default messages.
     *
     * @param string $key
     *
     * @return string
     */
    public function getDefaultMessage($key)
    {
        return $this->defaultMessages[$key] ?? '';
    }

    /**
     * Gets all default messages.
     *
     * @return string[]
     */
    public function getDefaultMessages()
    {
        return $this->defaultMessages;
    }

    /**
     * Gets one error.
     *
     * @param string $param
     * @param string $key
     * @param string $group
     *
     * @return string
     */
    public function getError($param, $key = null, $group = null)
    {
        if (null === $key) {
            return $this->getFirstError($param, $group);
        }

        if (null !== $group) {
            return $this->errors[$group][$param][$key] ?? '';
        }

        return $this->errors[$param][$key] ?? '';
    }

    /**
     * Gets multiple errors.
     *
     * @param string $param
     * @param string $group
     *
     * @return string[]
     */
    public function getErrors($param = null, $group = null)
    {
        if (null !== $param) {
            if (null !== $group) {
                return $this->errors[$group][$param] ?? [];
            }

            return $this->errors[$param] ?? [];
        }

        return $this->errors;
    }

    /**
     * Gets the first error of a parameter.
     *
     * @param string $param
     * @param string $group
     *
     * @return string
     */
    public function getFirstError($param, $group = null)
    {
        if (null !== $group) {
            if (isset($this->errors[$group][$param])) {
                $first = array_slice($this->errors[$group][$param], 0, 1);

                return array_shift($first);
            }
        }

        if (isset($this->errors[$param])) {
            $first = array_slice($this->errors[$param], 0, 1);

            return array_shift($first);
        }

        return '';
    }

    /**
     * Gets the value of a request parameter in validated data.
     *
     * @param string $param
     * @param string $group
     *
     * @return string
     */
    public function getValue($param, $group = null)
    {
        if (null !== $group) {
            return $this->values[$group][$param] ?? '';
        }

        return $this->values[$param] ?? '';
    }

    /**
     * Gets the validated data.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Gets the error storage mode.
     *
     * @return int
     */
    public function getErrorStorageMode()
    {
        return $this->errorStorageMode;
    }

    /**
     * Removes validation errors.
     *
     * @param string $param
     * @param string $group
     *
     * @return self
     */
    public function removeErrors($param = null, $group = null)
    {
        if (null !== $group) {
            if (null !== $param) {
                if (isset($this->errors[$group][$param])) {
                    $this->errors[$group][$param] = [];
                }
            } elseif (isset($this->errors[$group])) {
                $this->errors[$group] = [];
            }
        } elseif (null !== $param) {
            if (isset($this->errors[$param])) {
                $this->errors[$param] = [];
            }
        }

        return $this;
    }

    /**
     * Sets the default error message for a validation rule.
     *
     * @param string $rule
     * @param string $message
     *
     * @return self
     */
    public function setDefaultMessage($rule, $message)
    {
        $this->defaultMessages[$rule] = $message;

        return $this;
    }

    /**
     * Sets default error messages.
     *
     * @param string[] $messages
     *
     * @return self
     */
    public function setDefaultMessages(array $messages)
    {
        $this->defaultMessages = $messages;

        return $this;
    }

    /**
     * Sets all errors.
     *
     * @param string[] $errors
     *
     * @return self
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Sets the error storage mode.
     *
     * @param int $errorStorageMode
     *
     * @return self
     */
    public function setErrorStorageMode(int $errorStorageMode)
    {
        $this->errorStorageMode = $errorStorageMode;

        return $this;
    }

    /**
     * Sets the errors of a parameter.
     *
     * @param string   $param
     * @param string[] $errors
     * @param string   $group
     *
     * @return self
     */
    public function setParamErrors($param, array $errors, $group = null)
    {
        if (null !== $group) {
            $this->errors[$group][$param] = $errors;
        } else {
            $this->errors[$param] = $errors;
        }

        return $this;
    }

    /**
     * Sets the value of a request parameter.
     *
     * @param string $param
     * @param mixed  $value
     * @param string $group
     *
     * @return self
     */
    public function setValue($param, $value, $group = null)
    {
        if (null !== $group) {
            $this->values[$group][$param] = $value;
        } else {
            $this->values[$param] = $value;
        }

        return $this;
    }

    /**
     * Sets the values of request parameters.
     *
     * @param array $values
     *
     * @return self
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Gets the value of a property of an object.
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getPropertyValue($object, string $propertyName, $default = null)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The first argument should be an object');
        }

        if (!property_exists($object, $propertyName)) {
            return $default;
        }

        $reflectionProperty = new ReflectionProperty($object, $propertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Fetch request parameter value from body or query string (in that order).
     *
     * @param  Request $request
     * @param  string  $key The parameter key.
     * @param  string  $default The default value.
     *
     * @return mixed The parameter value.
     */
    protected function getRequestParam(Request $request, $key, $default = null)
    {
        $postParams = $request->getParsedBody();
        $getParams = $request->getQueryParams();

        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * Sets error messages after validation.
     *
     * @param NestedValidationException $e
     * @param string                    $param
     * @param AbstractComposite|array   $options
     * @param array                     $messages
     * @param string                    $group
     */
    protected function setMessages(NestedValidationException $e, $param, $options, array $messages = [], $group = null)
    {
        $paramRules = $options instanceof RespectValidator ? $options->getRules() : $options['rules']->getRules();

        // Get the names of all rules used for this param
        $rulesNames = [];
        foreach ($paramRules as $rule) {
            $rulesNames[] = lcfirst((new ReflectionClass($rule))->getShortName());
        }

        $params = [
            $e->findMessages($rulesNames)
        ];

        // If default messages are defined
        if (!empty($this->defaultMessages)) {
            $params[] = $e->findMessages($this->defaultMessages);
        }

        // If global messages are defined
        if (!empty($messages)) {
            $params[] = $e->findMessages($messages);
        }

        // If individual messages are defined
        if (is_array($options) && isset($options['messages'])) {
            if (!is_array($options['messages'])) {
                throw new InvalidArgumentException(sprintf('Expected custom individual messages to be of type array, %s given', gettype($options['messages'])));
            }

            $params[] = $e->findMessages($options['messages']);
        }

        $errors = array_filter(call_user_func_array('array_merge', $params));

        if (null !== $group) {
            $this->errors[$group][$param] = $this->errorStorageMode === self::MODE_ASSOCIATIVE ? $errors : array_values($errors);
        } else {
            $this->errors[$param] = $this->errorStorageMode === self::MODE_ASSOCIATIVE ? $errors : array_values($errors);
        }
    }
}
