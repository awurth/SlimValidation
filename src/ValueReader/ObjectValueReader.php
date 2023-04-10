<?php

declare(strict_types=1);

namespace Awurth\Validator\ValueReader;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ObjectValueReader implements ValueReaderInterface
{
    private static ?PropertyAccessor $propertyAccessor = null;

    public function getValue(mixed $subject, string $path, mixed $default = null): mixed
    {
        return self::getPropertyAccessor()->isReadable($subject, $path)
            ? self::getPropertyAccessor()->getValue($subject, $path)
            : $default
        ;
    }

    public function supports(mixed $subject): bool
    {
        return \is_object($subject);
    }

    private static function getPropertyAccessor(): PropertyAccessor
    {
        if (null === self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
