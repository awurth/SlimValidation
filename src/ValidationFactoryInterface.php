<?php

declare(strict_types=1);

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
