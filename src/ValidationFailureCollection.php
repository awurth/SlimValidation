<?php

/*
 * This file is part of the Awurth Validator package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\Validator;

use ArrayIterator;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

/**
 * Holds a list of validation failures.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class ValidationFailureCollection implements ValidationFailureCollectionInterface, IteratorAggregate
{
    /**
     * @var ValidationFailureInterface[]
     */
    private array $failures = [];

    public function __construct(iterable $failures = [])
    {
        $this->addAll($failures);
    }

    public function filter(callable $callback): self
    {
        return new self(\array_filter($this->failures, $callback));
    }

    public function find(callable $callback): ?ValidationFailureInterface
    {
        foreach ($this->failures as $failure) {
            if ($callback($failure)) {
                return $failure;
            }
        }

        return null;
    }

    public function add(ValidationFailureInterface $failure): void
    {
        $this->failures[] = $failure;
    }

    public function addAll(iterable $failures): void
    {
        foreach ($failures as $failure) {
            $this->add($failure);
        }
    }

    public function get(int $offset): ValidationFailureInterface
    {
        if (!isset($this->failures[$offset])) {
            throw new OutOfBoundsException(\sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->failures[$offset];
    }

    public function has(int $offset): bool
    {
        return isset($this->failures[$offset]);
    }

    public function set(int $offset, ValidationFailureInterface $failure): void
    {
        $this->failures[$offset] = $failure;
    }

    public function remove(int $offset): void
    {
        unset($this->failures[$offset]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    public function count(): int
    {
        return \count($this->failures);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->failures);
    }
}
