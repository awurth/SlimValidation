<?php

/*
 * This file is part of the awurth/slim-validation package.
 *
 * (c) Alexis Wurth <alexis.wurth57@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\SlimValidation;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReflectionClass;
use ReflectionProperty;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AllOf;

/**
 * Validator.
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 */
class Validator
{
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
     * with validation rules as the key, or in an indexed array.
     *
     * @var bool
     */
    protected $showValidationRules;

    /**
     * The validated data.
     *
     * @var array
     */
    protected $values;

    /**
     * Constructor.
     *
     * @param bool     $showValidationRules
     * @param string[] $defaultMessages
     */
    public function __construct(bool $showValidationRules = true, array $defaultMessages = [])
    {
        $this->showValidationRules = $showValidationRules;
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
     * @param array    $array
     * @param array    $rules
     * @param string   $group
     * @param string[] $messages
     * @param mixed    $default
     *
     * @return self
     */
    public function array(array $array, array $rules, $group = null, array $messages = [], $default = null)
    {
        foreach ($rules as $key => $options) {
            $config = new Configuration($rules, $key, $group, $default);

            $value = $array[$key] ?? $config->getDefault();

            $this->validateInput($value, $config, $messages);
        }

        return $this;
    }

    /**
     * Validates an objects properties with the given rules.
     *
     * @param object   $object
     * @param array    $rules
     * @param string   $group
     * @param string[] $messages
     * @param mixed    $default
     *
     * @return self
     */
    public function object($object, array $rules, $group = null, array $messages = [], $default = null)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The first argument should be an object');
        }

        foreach ($rules as $property => $options) {
            $config = new Configuration($rules, null, $group, $default);

            $value = $this->getPropertyValue($object, $property, $config->getDefault());

            $this->validateInput($value, $config, $messages);
        }

        return $this;
    }

    /**
     * Validates request parameters with the given rules.
     *
     * @param Request  $request
     * @param array    $rules
     * @param string   $group
     * @param string[] $messages
     * @param mixed    $default
     *
     * @return self
     */
    public function request(Request $request, array $rules, $group = null, array $messages = [], $default = null)
    {
        foreach ($rules as $param => $options) {
            $config = new Configuration($rules, $param, $group, $default);

            $value = $this->getRequestParam($request, $param, $config->getDefault());

            $this->validateInput($value, $config, $messages);
        }

        return $this;
    }

    /**
     * Validates request parameters, an array or an objects properties.
     *
     * @param Request|object|array $input
     * @param array                $rules
     * @param string               $group
     * @param string[]             $messages
     * @param mixed                $default
     *
     * @return self
     */
    public function validate($input, array $rules, $group = null, array $messages = [], $default = null)
    {
        if ($input instanceof Request) {
            return $this->request($input, $rules, $group, $messages, $default);
        } elseif (is_array($input)) {
            return $this->array($input, $rules, $group, $messages, $default);
        } elseif (is_object($input)) {
            return $this->object($input, $rules, $group, $messages, $default);
        }

        return $this->value($input, $rules, null, $group, $messages);
    }

    /**
     * Validates a single value with the given rules.
     *
     * @param mixed       $value
     * @param AllOf|array $rules
     * @param string      $key
     * @param string      $group
     * @param string[]    $messages
     *
     * @return self
     */
    public function value($value, $rules, $key, $group = null, array $messages = [])
    {
        $config = new Configuration($rules, $key, $group);

        $this->validateInput($value, $config, $messages);

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
     * Adds a validation error.
     *
     * @param string $key
     * @param string $message
     * @param string $group
     *
     * @return self
     */
    public function addError($key, $message, $group = null)
    {
        if (!empty($group)) {
            $this->errors[$group][$key][] = $message;
        } else {
            $this->errors[$key][] = $message;
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
     * @param string $key
     * @param string $index
     * @param string $group
     *
     * @return string
     */
    public function getError($key, $index = null, $group = null)
    {
        if (null === $index) {
            return $this->getFirstError($key, $group);
        }

        if (!empty($group)) {
            return $this->errors[$group][$key][$index] ?? '';
        }

        return $this->errors[$key][$index] ?? '';
    }

    /**
     * Gets multiple errors.
     *
     * @param string $key
     * @param string $group
     *
     * @return string[]
     */
    public function getErrors($key = null, $group = null)
    {
        if (!empty($key)) {
            if (!empty($group)) {
                return $this->errors[$group][$key] ?? [];
            }

            return $this->errors[$key] ?? [];
        }

        return $this->errors;
    }

    /**
     * Gets the first error of a parameter.
     *
     * @param string $key
     * @param string $group
     *
     * @return string
     */
    public function getFirstError($key, $group = null)
    {
        if (!empty($group)) {
            if (isset($this->errors[$group][$key])) {
                $first = array_slice($this->errors[$group][$key], 0, 1);

                return array_shift($first);
            }
        }

        if (isset($this->errors[$key])) {
            $first = array_slice($this->errors[$key], 0, 1);

            return array_shift($first);
        }

        return '';
    }

    /**
     * Gets a value from the validated data.
     *
     * @param string $key
     * @param string $group
     *
     * @return mixed
     */
    public function getValue($key, $group = null)
    {
        if (!empty($group)) {
            return $this->values[$group][$key] ?? null;
        }

        return $this->values[$key] ?? null;
    }

    /**
     * Gets the validated data.
     *
     * @param string $group
     *
     * @return array
     */
    public function getValues($group = null)
    {
        if (!empty($group)) {
            return $this->values[$group] ?? [];
        }

        return $this->values;
    }

    /**
     * Gets the errors storage mode.
     *
     * @return bool
     */
    public function getShowValidationRules()
    {
        return $this->showValidationRules;
    }

    /**
     * Removes validation errors.
     *
     * @param string $key
     * @param string $group
     *
     * @return self
     */
    public function removeErrors($key = null, $group = null)
    {
        if (!empty($group)) {
            if (!empty($key)) {
                if (isset($this->errors[$group][$key])) {
                    unset($this->errors[$group][$key]);
                }
            } elseif (isset($this->errors[$group])) {
                unset($this->errors[$group]);
            }
        } elseif (!empty($key)) {
            if (isset($this->errors[$key])) {
                unset($this->errors[$key]);
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
     * Sets validation errors.
     *
     * @param string[] $errors
     * @param string   $key
     * @param string   $group
     *
     * @return self
     */
    public function setErrors(array $errors, $key = null, $group = null)
    {
        if (!empty($group)) {
            if (!empty($key)) {
                $this->errors[$group][$key] = $errors;
            } else {
                $this->errors[$group] = $errors;
            }
        } elseif (!empty($key)) {
            $this->errors[$key] = $errors;
        } else {
            $this->errors = $errors;
        }

        return $this;
    }

    /**
     * Sets the errors storage mode.
     *
     * @param bool $showValidationRules
     *
     * @return self
     */
    public function setShowValidationRules(bool $showValidationRules)
    {
        $this->showValidationRules = $showValidationRules;

        return $this;
    }

    /**
     * Sets the value of a parameter.
     *
     * @param string $key
     * @param mixed  $value
     * @param string $group
     *
     * @return self
     */
    public function setValue($key, $value, $group = null)
    {
        if (!empty($group)) {
            $this->values[$group][$key] = $value;
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Sets values of validated data.
     *
     * @param array  $values
     * @param string $group
     *
     * @return self
     */
    public function setValues(array $values, $group = null)
    {
        if (!empty($group)) {
            $this->values[$group] = $values;
        } else {
            $this->values = $values;
        }

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
     * @param  string  $key
     * @param  string  $default
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
     * Handles a validation exception.
     *
     * @param NestedValidationException $e
     * @param Configuration             $config
     * @param string[]                  $messages
     */
    protected function handleValidationException(NestedValidationException $e, Configuration $config, array $messages = [])
    {
        if ($config->hasMessage()) {
            $this->setErrors([$config->getMessage()], $config->getKey(), $config->getGroup());
        } else {
            $this->storeErrors($e, $config, $messages);
        }
    }

    /**
     * Merges default messages, global messages and individual messages.
     *
     * @param array $errors
     *
     * @return string[]
     */
    protected function mergeMessages(array $errors)
    {
        $errors = array_filter(call_user_func_array('array_merge', $errors));

        return $this->showValidationRules ? $errors : array_values($errors);
    }

    /**
     * Sets error messages after validation.
     *
     * @param NestedValidationException $e
     * @param Configuration             $config
     * @param string[]                  $messages
     */
    protected function storeErrors(NestedValidationException $e, Configuration $config, array $messages = [])
    {
        $rules = $config->getValidationRules()->getRules();

        // Get the names of all rules used for this param
        $rulesNames = [];
        foreach ($rules as $rule) {
            $rulesNames[] = lcfirst((new ReflectionClass($rule))->getShortName());
        }

        $errors = [
            $e->findMessages($rulesNames)
        ];

        // If default messages are defined
        if (!empty($this->defaultMessages)) {
            $errors[] = $e->findMessages($this->defaultMessages);
        }

        // If global messages are defined
        if (!empty($messages)) {
            $errors[] = $e->findMessages($messages);
        }

        // If individual messages are defined
        if ($config->hasMessages()) {
            $errors[] = $e->findMessages($config->getMessages());
        }

        $this->setErrors($this->mergeMessages($errors), $config->getKey(), $config->getGroup());
    }

    /**
     * Executes the validation of a value and handles errors.
     *
     * @param mixed         $input
     * @param Configuration $config
     * @param string[]      $messages
     */
    protected function validateInput($input, Configuration $config, array $messages = [])
    {
        try {
            $config->getValidationRules()->assert($input);
        } catch (NestedValidationException $e) {
            $this->handleValidationException($e, $config, $messages);
        }

        $this->setValue($config->getKey(), $input, $config->getGroup());
    }
}
