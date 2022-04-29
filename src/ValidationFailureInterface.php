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
 * Represents a validation failure.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidationFailureInterface
{
    /**
     * Gets the validated data.
     */
    public function getInvalidValue(): mixed;

    /**
     * Gets the error message.
     */
    public function getMessage(): string;

    /**
     * Gets the object property, array key or request parameter.
     */
    public function getProperty(): ?string;

    /**
     * Gets the Respect/Validation rule name.
     */
    public function getRuleName(): ?string;
}
