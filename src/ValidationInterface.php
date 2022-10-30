<?php

declare(strict_types=1);

namespace Awurth\Validator;

use Respect\Validation\Validatable;

/**
 * Contains validation rules and other data used to handle validation failures.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidationInterface
{
    public function getRules(): Validatable;

    public function getProperty(): ?string;

    public function getDefault(): mixed;

    public function getMessage(): ?string;

    public function getMessages(): array;
}
