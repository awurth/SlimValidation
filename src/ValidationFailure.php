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

namespace Awurth\Validator;

/**
 * Represents a validation failure.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class ValidationFailure implements ValidationFailureInterface
{
    public function __construct(
        private readonly string $message,
        private readonly mixed $invalidValue,
        private readonly ?string $property = null,
        private readonly ?string $ruleName = null
    ) {
    }

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function getRuleName(): ?string
    {
        return $this->ruleName;
    }
}
