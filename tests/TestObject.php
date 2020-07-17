<?php

namespace Awurth\SlimValidation\Tests;

class TestObject
{
    private $privateProperty;

    protected $protectedProperty;

    public $publicProperty;

    public function __construct($privateProperty = null, $protectedProperty = null, $publicProperty = null)
    {
        $this->privateProperty = $privateProperty;
        $this->protectedProperty = $protectedProperty;
        $this->publicProperty = $publicProperty;
    }

    public function getProtectedProperty()
    {
        return $this->protectedProperty;
    }

    public function getPublicProperty()
    {
        return $this->publicProperty;
    }

    public function setPrivateProperty($privateProperty): void
    {
        $this->privateProperty = $privateProperty;
    }

    public function setProtectedProperty($protectedProperty): void
    {
        $this->protectedProperty = $protectedProperty;
    }

    public function setPublicProperty($publicProperty): void
    {
        $this->publicProperty = $publicProperty;
    }
}
