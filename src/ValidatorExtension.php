<?php

/*
 * This file is part of the awurth/slim-validation package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\SlimValidation;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Validator Twig Extension.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
class ValidatorExtension extends AbstractExtension
{
    /**
     * An array of names for Twig functions.
     *
     * @var string[]
     */
    protected $functionNames;

    /**
     * The validator instance.
     *
     * @var Validator
     */
    protected $validator;

    /**
     * Constructor.
     *
     * @param ValidatorInterface $validator     The validator instance
     * @param string[]           $functionNames An array of names for Twig functions
     */
    public function __construct(ValidatorInterface $validator, array $functionNames = [])
    {
        $this->validator = $validator;

        $this->functionNames['error'] = $functionNames['error'] ?? 'error';
        $this->functionNames['errors'] = $functionNames['errors'] ?? 'errors';
        $this->functionNames['has_error'] = $functionNames['has_error'] ?? 'has_error';
        $this->functionNames['has_errors'] = $functionNames['has_errors'] ?? 'has_errors';
        $this->functionNames['val'] = $functionNames['val'] ?? 'val';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction($this->functionNames['error'], [$this, 'getError']),
            new TwigFunction($this->functionNames['errors'], [$this, 'getErrors']),
            new TwigFunction($this->functionNames['has_error'], [$this, 'hasError']),
            new TwigFunction($this->functionNames['has_errors'], [$this, 'hasErrors']),
            new TwigFunction($this->functionNames['val'], [$this, 'getValue'])
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
