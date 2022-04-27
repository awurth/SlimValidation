<?php

namespace Awurth\Validator;

interface ValidationFailureInterface
{
    public function getInvalidValue(): mixed;

    public function getMessage(): string;

    public function getProperty(): ?string;

    public function getRuleName(): ?string;
}
