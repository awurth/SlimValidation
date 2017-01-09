<?php

namespace Awurth\Slim\Validation;

/**
 * ValidatorExtension
 *
 * @author  Alexis Wurth <alexis.wurth57@gmail.com>
 * @package Awurth\Slim\Validation
 */
class ValidatorExtension extends \Twig_Extension
{
    /**
     * Validator service
     *
     * @var Validator
     */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function getName()
    {
        return 'validator';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('error', [$this, 'getError']),
            new \Twig_SimpleFunction('errors', [$this, 'getErrors']),
            new \Twig_SimpleFunction('has_error', [$this, 'hasError']),
            new \Twig_SimpleFunction('has_errors', [$this, 'hasErrors']),
            new \Twig_SimpleFunction('val', [$this, 'getValue'])
        ];
    }

    /**
     * Get the first validation error of param
     *
     * @param string $param
     * @return string
     */
    public function getError($param)
    {
        return $this->validator->getFirst($param);
    }

    /**
     * Get the validation errors of param
     *
     * @param string $param
     * @return array
     */
    public function getErrors($param)
    {
        return $this->validator->getErrorsOf($param);
    }

    /**
     * Return true if there are validation errors for param
     *
     * @param string $param
     * @return bool
     */
    public function hasError($param)
    {
        return !empty($this->validator->getErrorsOf($param));
    }

    /**
     * Return true if there are validation errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !$this->validator->isValid();
    }

    /**
     * Get the value of a parameter in validated data
     *
     * @param string $param
     * @return string
     */
    public function getValue($param)
    {
        return $this->validator->getValue($param);
    }
}