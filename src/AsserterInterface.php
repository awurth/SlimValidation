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

interface AsserterInterface
{
    /**
     * @param array<string, string> $messages
     */
    public function assert(mixed $subject, ValidationInterface $validation, array $messages = []): ValidationFailureCollectionInterface;
}
