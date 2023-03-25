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
use Awurth\Validator\ValidationFailureInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Validator Twig Extension.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class LegacyValidatorExtension extends AbstractExtension
{
    /**
     * An array of names for Twig functions.
     *
     * @var string[]
     */
    private array $functionNames;

    /**
     * @param string[] $functionNames An array of names for Twig functions
     */
    public function __construct(private readonly StatefulValidatorInterface $validator, array $functionNames = [])
    {
        $this->functionNames['error'] = $functionNames['error'] ?? 'error';
        $this->functionNames['errors'] = $functionNames['errors'] ?? 'errors';
        $this->functionNames['has_error'] = $functionNames['has_error'] ?? 'has_error';
        $this->functionNames['has_errors'] = $functionNames['has_errors'] ?? 'has_errors';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->functionNames['error'], [$this, 'getError']),
            new TwigFunction($this->functionNames['errors'], [$this, 'getErrors']),
            new TwigFunction($this->functionNames['has_error'], [$this, 'hasError']),
            new TwigFunction($this->functionNames['has_errors'], [$this, 'hasErrors']),
        ];
    }

    public function getError(string $key, int $index = 0): ?string
    {
        $failures = $this->validator->getFailures()->filter(static fn (ValidationFailureInterface $failure): bool => $failure->getProperty() === $key);
        $failure = $failures->has($index) ? $failures->get(0) : null;

        return $failure?->getMessage();
    }

    /**
     * @return string[]
     */
    public function getErrors(?string $key = null): array
    {
        $failures = null === $key
            ? $this->validator->getFailures()
            : $this->validator->getFailures()->filter(static fn(ValidationFailureInterface $failure) => $failure->getProperty() === $key)
        ;

        return \array_map(
            static fn(ValidationFailureInterface $failure) => $failure->getMessage(),
            (array)$failures,
        );
    }

    public function hasError(string $key): bool
    {
        return $this->validator->getFailures()->find(static fn(ValidationFailureInterface $failure) => $failure->getProperty() === $key) !== null;
    }

    public function hasErrors(): bool
    {
        return $this->validator->getFailures()->count() !== 0;
    }
}
