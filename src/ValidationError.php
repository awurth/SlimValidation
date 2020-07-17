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

/**
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
class ValidationError
{
    private $invalidValue;
    private $message;
    private $path;
    private $name;

    public function __construct(string $path, string $message, $invalidValue)
    {
        $this->path = $path;
        $this->message = $message;
        $this->invalidValue = $invalidValue;
    }

    public function getInvalidValue()
    {
        return $this->invalidValue;
    }

    public function setInvalidValue($invalidValue): self
    {
        $this->invalidValue = $invalidValue;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
