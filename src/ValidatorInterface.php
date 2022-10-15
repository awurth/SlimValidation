<?php

declare(strict_types=1);

namespace Awurth\Validator;

use Respect\Validation\Validatable;

interface ValidatorInterface
{
    public function validate(mixed $subject, Validatable|array $rules, array $messages = []): ValidationFailureCollectionInterface;
}
