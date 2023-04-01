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

namespace Awurth\Validator\Failure;

use Awurth\Validator\ValidationInterface;

/**
 * Represents a validation failure.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidationFailureInterface
{
    public function getValidation(): ValidationInterface;

    /**
     * Gets the validated data.
     */
    public function getInvalidValue(): mixed;

    /**
     * Gets the error message.
     */
    public function getMessage(): string;

    /**
     * Gets the Respect/Validation rule name.
     */
    public function getRuleName(): ?string;
}
