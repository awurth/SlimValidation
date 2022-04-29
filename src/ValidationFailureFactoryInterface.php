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

/**
 * Handles the creation of a validation failure.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidationFailureFactoryInterface
{
    /**
     * Creates a new validation failure.
     *
     * @param string      $message      The error message
     * @param mixed       $invalidValue The invalid value
     * @param string|null $property     The object property, array key or request parameter
     * @param string|null $ruleName     The Respect/Validation rule name
     */
    public function create(string $message, mixed $invalidValue, ?string $property = null, ?string $ruleName = null): ValidationFailureInterface;
}
