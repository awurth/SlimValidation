<?php

/*
 * This file is part of the awurth/slim-validation package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\SlimValidation;

use Respect\Validation\Validatable as RespectValidatable;

/**
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
class Validatable
{
    /**
     * Default value for non-existent request parameters, object properties or array keys.
     *
     * @var mixed
     */
    private $default;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string[]
     */
    private $messages = [];

    /**
     * The path to use for errors and values storage.
     *
     * @var string
     */
    private $path;

    /**
     * @var RespectValidatable
     */
    private $rules;

    public function __construct(string $path, RespectValidatable $rules)
    {
        $this->path = $path;
        $this->rules = $rules;
    }

    /**
     * Gets the default value for non-existent request parameters, object properties or array keys.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default): self
    {
        $this->default = $default;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function getValidationRules(): RespectValidatable
    {
        return $this->rules;
    }

    public function setValidationRules(RespectValidatable $rules): self
    {
        $this->rules = $rules;

        return $this;
    }
}
