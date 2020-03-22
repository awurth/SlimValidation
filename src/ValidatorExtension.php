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

    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->functionNames['error'], [$this, 'getError']),
            new TwigFunction($this->functionNames['errors'], [$this, 'getErrors']),
            new TwigFunction($this->functionNames['has_error'], [$this, 'hasError']),
            new TwigFunction($this->functionNames['has_errors'], [$this, 'hasErrors']),
            new TwigFunction($this->functionNames['val'], [$this, 'getValue'])
        ];
    }

    public function getError(string $key, $index = null, $group = null): string
    {
        return $this->validator->getError($key, $index, $group);
    }

    public function getErrors(?string $key = null, ?string $group = null): array
    {
        return $this->validator->getErrors($key, $group);
    }

    public function getValue(string $key, ?string $group = null)
    {
        return $this->validator->getValue($key, $group);
    }

    public function hasError(string $key, ?string $group = null): bool
    {
        return !empty($this->validator->getErrors($key, $group));
    }

    public function hasErrors(): bool
    {
        return !$this->validator->isValid();
    }
}
