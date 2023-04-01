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

use Respect\Validation\Validatable;

/**
 * Contains validation rules and other data used to handle validation failures.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class Validation implements ValidationInterface
{
    public function __construct(
        private readonly Validatable $rules,
        private readonly ?string $property = null,
        private readonly mixed $default = null,
        private readonly ?string $message = null,
        private readonly array $messages = [],
        private readonly array $globalMessages = [],
    ) {
    }

    public function getRules(): Validatable
    {
        return $this->rules;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getGlobalMessages(): array
    {
        return $this->globalMessages;
    }
}
