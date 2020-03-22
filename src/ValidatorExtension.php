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
    private $functionNames;

    private $validator;

    /**
     * @param Validator $validator     The validator instance
     * @param string[]  $functionNames An array of names for Twig functions
     */
    public function __construct(Validator $validator, array $functionNames = [])
    {
        $this->validator = $validator;

        $this->functionNames['errors'] = $functionNames['errors'] ?? 'errors';
        $this->functionNames['has_error'] = $functionNames['has_error'] ?? 'has_error';
        $this->functionNames['has_errors'] = $functionNames['has_errors'] ?? 'has_errors';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->functionNames['errors'], [$this, 'getErrors']),
            new TwigFunction($this->functionNames['has_error'], [$this, 'hasError']),
            new TwigFunction($this->functionNames['has_errors'], [$this, 'hasErrors'])
        ];
    }

    public function getErrors(?string $path = null): ValidationErrorList
    {
        return null === $path ? $this->validator->getErrors() : $this->validator->getErrors()->findByPath($path);
    }

    public function hasError(string $path): bool
    {
        foreach ($this->validator->getErrors() as $error) {
            if ($error->getPath() === $path) {
                return true;
            }
        }

        return false;
    }

    public function hasErrors(): bool
    {
        return (bool)$this->validator->getErrors()->count();
    }
}
