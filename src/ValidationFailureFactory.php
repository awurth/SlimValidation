<?php

namespace Awurth\Validator;

final class ValidationFailureFactory implements ValidationFailureFactoryInterface
{
    public function create(
        string $message,
        mixed $invalidValue,
        ?string $property = null,
        ?string $ruleName = null
    ): ValidationFailureInterface
    {
        return new ValidationFailure($message, $invalidValue, $property, $ruleName);
    }
}
