<?php

namespace Awurth\SlimValidation\Tests;

use Awurth\SlimValidation\Validator;
use Awurth\SlimValidation\ValidatorExtension;
use PHPUnit\Framework\TestCase;

class ValidatorExtensionTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var ValidatorExtension
     */
    protected $validatorExtension;

    public function setUp(): void
    {
        $this->validator = new Validator();

        $this->validatorExtension = new ValidatorExtension($this->validator);
    }

    public function testGetError()
    {
        $this->assertEquals('', $this->validatorExtension->getError('username'));

        $this->validator->addError('username', 'Bad username');
        $this->validator->addError('username', 'Too short!');

        $this->assertEquals('Bad username', $this->validatorExtension->getError('username'));
    }

    public function testGetErrors()
    {
        $this->assertEquals([], $this->validatorExtension->getErrors());
        $this->assertEquals([], $this->validatorExtension->getErrors('username'));

        $this->validator->addError('username', 'Bad username');
        $this->validator->addError('password', 'Wrong password');

        $this->assertEquals([
            'username' => ['Bad username'],
            'password' => ['Wrong password']
        ], $this->validatorExtension->getErrors());

        $this->assertEquals(['Bad username'], $this->validatorExtension->getErrors('username'));
    }

    public function testGetErrorWithRule()
    {
        $this->assertEquals('', $this->validatorExtension->getError('username', 'notBlank'));

        $this->validator->setErrors([
            'username' => [
                'length' => 'Too short!',
                'notBlank' => 'Required'
            ]
        ]);

        $this->assertEquals('Required', $this->validatorExtension->getError('username', 'notBlank'));
    }

    public function testGetValue()
    {
        $this->assertEquals('', $this->validatorExtension->getValue('username'));

        $this->validator->setValues(['username' => 'awurth']);

        $this->assertEquals('awurth', $this->validatorExtension->getValue('username'));
    }

    public function testHasError()
    {
        $this->assertFalse($this->validatorExtension->hasError('username'));

        $this->validator->addError('username', 'Bad username');

        $this->assertTrue($this->validatorExtension->hasError('username'));
    }

    public function testHasErrors()
    {
        $this->assertFalse($this->validatorExtension->hasErrors());

        $this->validator->addError('username', 'Bad username');

        $this->assertTrue($this->validatorExtension->hasErrors());
    }
}
