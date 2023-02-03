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

/**
 * Handles the creation of a Validation.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidationFactoryInterface
{
    public function create(array $options, ?string $property = null, mixed $default = null): ValidationInterface;
}
