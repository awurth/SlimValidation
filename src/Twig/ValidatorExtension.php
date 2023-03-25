<?php

declare(strict_types=1);

/*
 * This file is part of the Awurth Validator package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\Validator\Twig;

use Awurth\Validator\StatefulValidatorInterface;
use Awurth\Validator\ValidationFailureCollectionInterface;
use Awurth\Validator\ValidationFailureInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Validator Twig Extension.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class ValidatorExtension extends AbstractExtension
{
    /**
     * An array of names for Twig functions.
     *
     * @var string[]
     */
    protected array $functionNames;

    /**
     * @param string[] $functionNames An array of names for Twig functions
     */
    public function __construct(private readonly StatefulValidatorInterface $validator, array $functionNames = [])
    {
        $this->functionNames['error'] = $functionNames['error'] ?? 'error';
        $this->functionNames['errors'] = $functionNames['errors'] ?? 'errors';
        $this->functionNames['has_errors'] = $functionNames['has_errors'] ?? 'has_errors';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->functionNames['error'], [$this, 'findFirst']),
            new TwigFunction($this->functionNames['errors'], [$this, 'findErrors']),
            new TwigFunction($this->functionNames['has_errors'], [$this, 'hasErrors']),
        ];
    }

    public function findFirst(?callable $callback = null): ?ValidationFailureInterface
    {
        if (null === $callback) {
            return $this->validator->getFailures()->has(0) ? $this->validator->getFailures()->get(0) : null;
        }

        return $this->validator->getFailures()->find($callback);
    }

    public function findErrors(?callable $callback = null): ValidationFailureCollectionInterface
    {
        if (null === $callback) {
            return $this->validator->getFailures();
        }

        return $this->validator->getFailures()->filter($callback);
    }

    public function hasErrors(): bool
    {
        return 0 !== $this->validator->getFailures()->count();
    }
}
