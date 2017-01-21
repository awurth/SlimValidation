<?php

namespace Awurth\Slim\Validation;

use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;

/**
 * Validator
 *
 * @author  Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Slim\Validation
 */
class Validator
{
    /**
     * List of validation errors
     *
     * @var array
     */
    protected $errors;

    /**
     * The validated data
     *
     * @var array
     */
    protected $data;

    /**
     * Validate request params with the given rules
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @return $this
     */
    public function validate(Request $request, array $rules, array $messages = [])
    {
        foreach ($rules as $param => $rule) {
            try {
                $value = $request->getParam($param);
                $this->data[$param] = $value;
                $rule->assert($value);
            } catch (NestedValidationException $e) {
                $rulesNames = [];
                foreach ($rule->getRules() as $r) {
                    $rulesNames[] = lcfirst((new \ReflectionClass($r))->getShortName());
                }

                $this->errors[$param] = array_filter(array_merge($e->findMessages($rulesNames), $e->findMessages($messages)));
            }
        }

        return $this;
    }

    /**
     * Add an error for param
     *
     * @param string $param
     * @param string $message
     * @return $this
     */
    public function addError($param, $message)
    {
        $this->errors[$param][] = $message;
        return $this;
    }

    /**
     * Add errors for param
     *
     * @param string $param
     * @param array $messages
     * @return $this
     */
    public function addErrors($param, array $messages)
    {
        foreach ($messages as $message) {
            $this->errors[$param][] = $message;
        }

        return $this;
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set all errors
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Get errors of param
     *
     * @param string $param
     * @return array
     */
    public function getErrorsOf($param)
    {
        return isset($this->errors[$param]) ? $this->errors[$param] : [];
    }

    /**
     * Set errors of param
     *
     * @param string $param
     * @param array $errors
     * @return $this
     */
    public function setErrorsOf($param, array $errors)
    {
        $this->errors[$param] = $errors;
        return $this;
    }

    /**
     * Get first error of param
     *
     * @param string $param
     * @return string
     */
    public function getFirst($param)
    {
        if (isset($this->errors[$param])) {
            $first = array_slice($this->errors[$param], 0, 1);
            return array_shift($first);
        }

        return '';
    }

    /**
     * Get the value of a parameter in validated data
     *
     * @param string $param
     * @return string
     */
    public function getValue($param)
    {
        return isset($this->data[$param]) ? $this->data[$param] : '';
    }

    /**
     * Set the value of parameters
     *
     * @param array $data
     * @return $this
     */
    public function setValues(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Set validator data
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get validated data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return true if there is no error
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }
}