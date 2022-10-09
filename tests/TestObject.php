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
