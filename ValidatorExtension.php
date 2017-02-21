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

    /**
     * Array of names for Twig functions
     *
     * @var array
     */
    private $functionsNames;

    /**
     * Constructor
     *
     * @param Validator $validator The validator instance
     * @param array $functionsNames An array of names for Twig functions
     */
    public function __construct(Validator $validator, $functionsNames = [])
    {
        $this->validator = $validator;

        $this->functionsNames['error'] = !empty($functionsNames['error']) ? $functionsNames['error'] : 'error';
        $this->functionsNames['errors'] = !empty($functionsNames['errors']) ? $functionsNames['errors'] : 'errors';
        $this->functionsNames['has_error'] = !empty($functionsNames['has_error']) ? $functionsNames['has_error'] : 'has_error';
        $this->functionsNames['has_errors'] = !empty($functionsNames['has_errors']) ? $functionsNames['has_errors'] : 'has_errors';
        $this->functionsNames['val'] = !empty($functionsNames['val']) ? $functionsNames['val'] : 'val';
    }

    public function getName()
    {
        return 'validator';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction($this->functionsNames['error'], [$this, 'getError']),
            new \Twig_SimpleFunction($this->functionsNames['errors'], [$this, 'getErrors']),
            new \Twig_SimpleFunction($this->functionsNames['has_error'], [$this, 'hasError']),
            new \Twig_SimpleFunction($this->functionsNames['has_errors'], [$this, 'hasErrors']),
            new \Twig_SimpleFunction($this->functionsNames['val'], [$this, 'getValue'])
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
    public function getErrors($param = null)
    {
        return $param ? $this->validator->getErrorsOf($param) : $this->validator->getErrors();
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
