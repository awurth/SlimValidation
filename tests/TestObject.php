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

    public function getPrivateProperty()
    {
        return $this->privateProperty;
    }

    public function getProtectedProperty()
    {
        return $this->protectedProperty;
    }

    public function getPublicProperty()
    {
        return $this->publicProperty;
    }

    public function setPrivateProperty($privateProperty)
    {
        $this->privateProperty = $privateProperty;
    }

    public function setProtectedProperty($protectedProperty)
    {
        $this->protectedProperty = $protectedProperty;
    }

    public function setPublicProperty($publicProperty)
    {
        $this->publicProperty = $publicProperty;
    }
}
