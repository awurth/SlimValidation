<?php

namespace Awurth\SlimValidation;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReflectionClass;
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
     * @var array
     */
    protected $defaultMessages;

    /**
     * The list of validation errors.
     *
     * @var array
     */
    protected $errors;

    /**
     * Tells if errors should be stored in an associative array
     * or in an indexed array.
     *
     * @var int
     */
    protected $errorStorageMode;

    /**
     * Constructor.
     *
     * @param int $errorStorageMode
     * @param array $defaultMessages
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
     * Validates request parameters with the given rules.
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     *
     * @return $this
     */
    public function validate(Request $request, array $rules, array $messages = [])
    {
        foreach ($rules as $param => $options) {
            $value = $this->getRequestParam($request, $param);

            try {
                if ($options instanceof RespectValidator) {
                    $options->assert($value);
                } else {
                    if (!is_array($options) || !isset($options['rules']) || !($options['rules'] instanceof RespectValidator)) {
                        throw new InvalidArgumentException('Validation rules are missing');
                    }

                    $options['rules']->assert($value);
                }
            } catch (NestedValidationException $e) {
                // If the 'message' key exists, set it as only message for this param
                if (is_array($options) && isset($options['message'])) {
                    if (!is_string($options['message'])) {
                        throw new InvalidArgumentException(sprintf('Expected custom message to be of type string, %s given', gettype($options['message'])));
                    }

                    $this->errors[$param] = [$options['message']];
                } else {
                    // If the 'messages' key exists, override global messages
                    $this->setMessages($e, $param, $options, $messages);
                }
            }

            $this->values[$param] = $value;
        }

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
     *
     * @return $this
     */
    public function addError($param, $message)
    {
        $this->errors[$param][] = $message;

        return $this;
    }

    /**
     * Gets all default messages.
     *
     * @return array
     */
    public function getDefaultMessages()
    {
        return $this->defaultMessages;
    }

    /**
     * Gets all errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Gets the first error of a parameter.
     *
     * @param string $param
     *
     * @return string
     */
    public function getFirstError($param)
    {
        if (isset($this->errors[$param])) {
            $first = array_slice($this->errors[$param], 0, 1);

            return array_shift($first);
        }

        return '';
    }

    /**
     * Gets errors of a parameter.
     *
     * @param string $param
     *
     * @return array
     */
    public function getParamErrors($param)
    {
        return isset($this->errors[$param]) ? $this->errors[$param] : [];
    }

    /**
     * Gets the error of a validation rule for a parameter.
     *
     * @param string $param
     * @param string $rule
     *
     * @return string
     */
    public function getParamRuleError($param, $rule)
    {
        return isset($this->errors[$param][$rule]) ? $this->errors[$param][$rule] : '';
    }

    /**
     * Gets the value of a request parameter in validated data.
     *
     * @param string $param
     *
     * @return string
     */
    public function getValue($param)
    {
        return isset($this->values[$param]) ? $this->values[$param] : '';
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
     * Sets the default error message for a validation rule.
     *
     * @param string $rule
     * @param string $message
     *
     * @return $this
     */
    public function setDefaultMessage($rule, $message)
    {
        $this->defaultMessages[$rule] = $message;

        return $this;
    }

    /**
     * Sets default error messages.
     *
     * @param array $messages
     *
     * @return $this
     */
    public function setDefaultMessages(array $messages)
    {
        $this->defaultMessages = $messages;

        return $this;
    }

    /**
     * Sets all errors.
     *
     * @param array $errors
     *
     * @return $this
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
     * @return $this
     */
    public function setErrorStorageMode(int $errorStorageMode)
    {
        $this->errorStorageMode = $errorStorageMode;

        return $this;
    }

    /**
     * Sets the errors of a parameter.
     *
     * @param string $param
     * @param array $errors
     *
     * @return $this
     */
    public function setParamErrors($param, array $errors)
    {
        $this->errors[$param] = $errors;

        return $this;
    }

    /**
     * Sets the value of a request parameter.
     *
     * @param string $param
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($param, $value)
    {
        $this->values[$param] = $value;

        return $this;
    }

    /**
     * Sets the values of request parameters.
     *
     * @param array $values
     *
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
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
     * @param string $param
     * @param AbstractComposite|array $options
     * @param array $messages
     */
    protected function setMessages(NestedValidationException $e, $param, $options, array $messages)
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

        $this->errors[$param] = $this->errorStorageMode === self::MODE_ASSOCIATIVE ? $errors : array_values($errors);
    }
}
