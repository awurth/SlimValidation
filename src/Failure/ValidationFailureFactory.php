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
 * Handles the creation of a validation failure.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class ValidationFailureFactory implements ValidationFailureFactoryInterface
{
    public function create(
        ValidationInterface $validation,
        string $message,
        mixed $invalidValue,
        ?string $ruleName = null,
    ): ValidationFailureInterface {
        return new ValidationFailure($validation, $message, $invalidValue, $ruleName);
    }
}
