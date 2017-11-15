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

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ValidatorExtension.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
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
     * @param string $key
     * @param string $index
     * @param string $group
     *
     * @return string
     */
    public function getError($key, $index = null, $group = null)
    {
        return $this->validator->getError($key, $index, $group);
    }

    /**
     * Gets validation errors.
     *
     * @param string $key
     * @param string $group
     *
     * @return string[]
     */
    public function getErrors($key = null, $group = null)
    {
        return $this->validator->getErrors($key, $group);
    }

    /**
     * Gets a value from the validated data.
     *
     * @param string $key
     * @param string $group
     *
     * @return string
     */
    public function getValue($key, $group = null)
    {
        return $this->validator->getValue($key, $group);
    }

    /**
     * Tells whether there are validation errors for a parameter.
     *
     * @param string $key
     * @param string $group
     *
     * @return bool
     */
    public function hasError($key, $group = null)
    {
        return !empty($this->validator->getErrors($key, $group));
    }

    /**
     * Tells whether there are validation errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !$this->validator->isValid();
    }
}
