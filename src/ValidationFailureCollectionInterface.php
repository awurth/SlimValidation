<?php

namespace Awurth\Validator;

use ArrayAccess;
use Countable;
use Traversable;

interface ValidationFailureCollectionInterface extends ArrayAccess, Countable, Traversable
{
    public function add(ValidationFailureInterface $failure): void;

    public function addAll(iterable $failures): void;

    public function get(int $offset): ValidationFailureInterface;

    public function has(int $offset): bool;

    public function set(int $offset, ValidationFailureInterface $failure): void;

    public function remove(int $offset): void;
}
