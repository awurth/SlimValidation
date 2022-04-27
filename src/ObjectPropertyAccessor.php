<?php

namespace Awurth\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ObjectPropertyAccessor
{
    private static ?PropertyAccessor $propertyAccessor = null;

    public static function getValue(object $object, string $property, mixed $default = null): mixed
    {
        return self::getPropertyAccessor()->isReadable($object, $property)
            ? self::getPropertyAccessor()->getValue($object, $property)
            : $default;
    }

    private static function getPropertyAccessor(): PropertyAccessor
    {
        if (null === self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
