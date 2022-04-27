<?php

namespace Awurth\Validator;

final class ValidationFailureCollectionFactory implements ValidationFailureCollectionFactoryInterface
{
    public function create(iterable $failures = []): ValidationFailureCollectionInterface
    {
        return new ValidationFailureCollection($failures);
    }
}
