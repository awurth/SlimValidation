<?php

declare(strict_types=1);

/*
 * This file is part of the Awurth Validator package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Allows accessing an object's properties.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
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
