<?php

namespace Awurth\Validator;

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
