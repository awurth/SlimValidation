<?php

declare(strict_types=1);

namespace Awurth\Validator\ValueReader;

interface ValueReaderInterface
{
    public function getValue(mixed $subject, string $path, mixed $default = null): mixed;

    public function supports(mixed $subject): bool;
}
