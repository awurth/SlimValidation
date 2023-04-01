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

use Awurth\Validator\Failure\ValidationFailureCollectionInterface;
use Respect\Validation\Validatable;

interface ValidatorInterface
{
    /**
     * @param array<string, string> $messages
     */
    public function validate(mixed $subject, Validatable|array $rules, array $messages = [], mixed $context = null): ValidationFailureCollectionInterface;
}
