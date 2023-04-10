<?php

declare(strict_types=1);

namespace Awurth\Validator\ValueReader;

interface ValueReaderRegistryInterface
{
    public function getValueReaderFor(mixed $subject): ValueReaderInterface;
}
