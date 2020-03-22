<?php

namespace Awurth\SlimValidation;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;

class ValidationErrorList implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var ValidationError[]
     */
    private $errors = [];

    public function __construct(array $errors = [])
    {
        foreach ($errors as $error) {
            $this->add($error);
        }
    }

    public function add(ValidationError $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    public function findByPath(string $path): self
    {
        $errors = new static();
        foreach ($this->errors as $error) {
            if ($error->getPath() === $path) {
                $errors->add($error);
            }
        }

        return $errors;
    }

    public function get(int $offset): ValidationError
    {
        if (!isset($this->errors[$offset])) {
            throw new OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->errors[$offset];
    }

    public function has(int $offset): bool
    {
        return isset($this->errors[$offset]);
    }

    public function remove(int $offset): self
    {
        unset($this->errors[$offset]);

        return $this;
    }

    public function set(int $offset, ValidationError $error): self
    {
        $this->errors[$offset] = $error;

        return $this;
    }

    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * @return ArrayIterator|ValidationError[]
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->errors);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): ValidationError
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $error): void
    {
        if (null === $offset) {
            $this->add($error);
        } else {
            $this->set($offset, $error);
        }
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }
}
