<?php

namespace Awurth\SlimValidation;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ValidatorExtension.
 *
 * @author Alexis Wurth <alexis.wurth57@gmail.com>
 */
class ValidatorExtension extends AbstractExtension
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

        $this->functionsNames['error'] = $functionsNames['error'] ?? 'error';
        $this->functionsNames['errors'] = $functionsNames['errors'] ?? 'errors';
        $this->functionsNames['has_error'] = $functionsNames['has_error'] ?? 'has_error';
        $this->functionsNames['has_errors'] = $functionsNames['has_errors'] ?? 'has_errors';
        $this->functionsNames['val'] = $functionsNames['val'] ?? 'val';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction($this->functionsNames['error'], [$this, 'getError']),
            new TwigFunction($this->functionsNames['errors'], [$this, 'getErrors']),
            new TwigFunction($this->functionsNames['has_error'], [$this, 'hasError']),
            new TwigFunction($this->functionsNames['has_errors'], [$this, 'hasErrors']),
            new TwigFunction($this->functionsNames['val'], [$this, 'getValue'])
        ];
    }

    /**
     * Gets the first validation error of a parameter.
     *
     * @param string $param
     * @param string $key
     * @param string $group
     *
     * @return string
     */
    public function getError($param, $key = null, $group = null)
    {
        return $this->validator->getError($param, $key, $group);
    }

    /**
     * Gets the validation errors of a parameter.
     *
     * @param string $param
     * @param string $group
     *
     * @return string[]
     */
    public function getErrors($param = null, $group = null)
    {
        return $this->validator->getErrors($param, $group);
    }

    /**
     * Gets the value of a parameter in validated data.
     *
     * @param string $param
     * @param string $group
     *
     * @return string
     */
    public function getValue($param, $group = null)
    {
        return $this->validator->getValue($param, $group);
    }

    /**
     * Tells if there are validation errors for a parameter.
     *
     * @param string $param
     * @param string $group
     *
     * @return bool
     */
    public function hasError($param, $group = null)
    {
        return !empty($this->validator->getErrors($param, $group));
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
