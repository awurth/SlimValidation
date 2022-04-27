<?php

namespace Awurth\Validator;

interface ValidationFailureFactoryInterface
{
    public function create(string $message, mixed $invalidValue, ?string $property = null, ?string $ruleName = null): ValidationFailureInterface;
}
