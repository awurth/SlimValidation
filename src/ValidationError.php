<?php

namespace Awurth\SlimValidation;

class ValidationError
{
    private $invalidValue;
    private $message;
    private $path;
    private $rule;

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

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(?string $rule): self
    {
        $this->rule = $rule;

        return $this;
    }
}
