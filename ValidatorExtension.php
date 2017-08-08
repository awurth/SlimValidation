<?php

namespace Awurth\SlimValidation;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * ValidatorExtension.
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 */
class ValidatorExtension extends Twig_Extension
{
    /**
     * Array of names for Twig functions.
     *
     * @var array
     */
    protected $functionsNames;

    /**
     * Validator service.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * Constructor.
     *
     * @param Validator $validator The validator instance
     * @param array $functionsNames An array of names for Twig functions
     */
    public function __construct(Validator $validator, $functionsNames = [])
    {
        $this->validator = $validator;

        $this->functionsNames['error'] = !empty($functionsNames['error']) ? $functionsNames['error'] : 'error';
        $this->functionsNames['errors'] = !empty($functionsNames['errors']) ? $functionsNames['errors'] : 'errors';
        $this->functionsNames['rule_error'] = !empty($functionsNames['rule_error']) ? $functionsNames['rule_error'] : 'rule_error';
        $this->functionsNames['has_error'] = !empty($functionsNames['has_error']) ? $functionsNames['has_error'] : 'has_error';
        $this->functionsNames['has_errors'] = !empty($functionsNames['has_errors']) ? $functionsNames['has_errors'] : 'has_errors';
        $this->functionsNames['val'] = !empty($functionsNames['val']) ? $functionsNames['val'] : 'val';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction($this->functionsNames['error'], [$this, 'getError']),
            new Twig_SimpleFunction($this->functionsNames['errors'], [$this, 'getErrors']),
            new Twig_SimpleFunction($this->functionsNames['rule_error'], [$this, 'getRuleError']),
            new Twig_SimpleFunction($this->functionsNames['has_error'], [$this, 'hasError']),
            new Twig_SimpleFunction($this->functionsNames['has_errors'], [$this, 'hasErrors']),
            new Twig_SimpleFunction($this->functionsNames['val'], [$this, 'getValue'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'validator';
    }

    /**
     * Gets the first validation error of a parameter.
     *
     * @param string $param
     *
     * @return string
     */
    public function getError($param)
    {
        return $this->validator->getFirstError($param);
    }

    /**
     * Gets the validation errors of a parameter.
     *
     * @param string $param
     *
     * @return array
     */
    public function getErrors($param = '')
    {
        return $param ? $this->validator->getParamErrors($param) : $this->validator->getErrors();
    }

    /**
     * Gets the error of a rule for a parameter.
     *
     * @param string $param
     * @param string $rule
     *
     * @return string
     */
    public function getRuleError($param, $rule)
    {
        return $this->validator->getParamRuleError($param, $rule);
    }

    /**
     * Gets the value of a parameter in validated data.
     *
     * @param string $param
     *
     * @return string
     */
    public function getValue($param)
    {
        return $this->validator->getValue($param);
    }

    /**
     * Tells if there are validation errors for a parameter.
     *
     * @param string $param
     *
     * @return bool
     */
    public function hasError($param)
    {
        return !empty($this->validator->getParamErrors($param));
    }

    /**
     * Tells if there are validation errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !$this->validator->isValid();
    }
}
