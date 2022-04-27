<?php

namespace Awurth\Validator;

interface ValidationFailureCollectionFactoryInterface
{
    public function create(iterable $failures = []): ValidationFailureCollectionInterface;
}
