<?php

namespace Awurth\SlimValidation\Tests;

use Awurth\SlimValidation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as V;
use Slim\Http\Environment;
use Slim\Http\Request;

class ValidatorTest extends TestCase
{
    /**
     * @var array
     */
    protected $array;

    /**
     * @var TestObject
     */
    protected $object;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Validator
     */
    protected $validator;

    public function setUp()
    {
        $this->request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'username=a_wurth&password=1234'
        ]));

        $this->array = [
            'username' => 'a_wurth',
            'password' => '1234'
        ];

        $this->object = new TestObject('private', 'protected', 'public');

        $this->validator = new Validator();
    }

    /**
     * @expectedException Error
     */
    public function testValidateWithoutRules(): void
    {
        $this->validator->validateRequest($this->request, ['username']);
    }

    /**
     * @expectedException Error
     */
    public function testValidateWithOptionsWrongType(): void
    {
        $this->validator->validateRequest($this->request, ['username' => null]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateWithRulesWrongType(): void
    {
        $this->validator->validateRequest($this->request, [
            'username' => [
                'rules' => null
            ]
        ]);
    }

    public function testRequest(): void
    {
        $errors = $this->validator->validateRequest($this->request, ['username' => V::length(6)]);

        $this->assertSame(0, $errors->count());
    }

    public function testArray(): void
    {
        $errors = $this->validator->validateArray($this->array, [
            'username' => V::notBlank(),
            'password' => V::notBlank()
        ]);

        $this->assertSame(0, $errors->count());
    }

    public function testObject(): void
    {
        $errors = $this->validator->validateObject($this->object, [
            'privateProperty' => V::notBlank(),
            'protectedProperty' => V::notBlank(),
            'publicProperty' => V::notBlank()
        ]);

        $this->assertSame(1, $errors->count());
    }

    public function testValue(): void
    {
        $errors = $this->validator->validate(2017, V::numeric()->between(2010, 2020), 'year');

        $this->assertSame(0, $errors->count());
    }

    public function testValidateWithErrors(): void
    {
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8)
        ]);

        $this->assertSame(1, $errors->count());

        $error = $errors->get(0);

        $this->assertSame('username', $error->getPath());
        $this->assertSame('length', $error->getRule());
        $this->assertSame('a_wurth', $error->getInvalidValue());
        $this->assertSame('"a_wurth" must have a length greater than 8', $error->getMessage());
    }

    public function testValidateWithCustomDefaultMessage(): void
    {
        $this->validator->setDefaultMessages(['length' => 'Too short!']);
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8)
        ]);

        $this->assertSame(1, $errors->count());
        $this->assertSame('Too short!', $errors->get(0)->getMessage());
    }

    public function testValidateWithCustomGlobalMessages(): void
    {
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)
        ], ['length' => 'Too short!']);

        $this->assertSame(2, $errors->count());
        $this->assertSame('Too short!', $errors->get(0)->getMessage());
        $this->assertSame('Too short!', $errors->get(1)->getMessage());
    }

    public function testValidateWithCustomDefaultAndGlobalMessages(): void
    {
        $this->validator->setDefaultMessage('length', 'Too short!');
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)->alpha()
        ], ['alpha' => 'Only letters are allowed']);

        $this->assertSame(3, $errors->count());
        $this->assertSame('Too short!', $errors->get(0)->getMessage());
        $this->assertSame('Too short!', $errors->get(1)->getMessage());
        $this->assertSame('Only letters are allowed', $errors->get(2)->getMessage());
        $this->assertSame('alpha', $errors->get(2)->getRule());
    }

    public function testValidateWithCustomIndividualMessage(): void
    {
        $errors = $this->validator->validateRequest($this->request, [
            'username' => [
                'rules' => V::length(8),
                'messages' => [
                    'length' => 'Too short!'
                ]
            ],
            'password' => V::length(8)
        ]);

        $this->assertSame(2, $errors->count());
        $this->assertSame('username', $errors->get(0)->getPath());
        $this->assertSame('Too short!', $errors->get(0)->getMessage());
        $this->assertSame('password', $errors->get(1)->getPath());
        $this->assertSame('"1234" must have a length greater than 8', $errors->get(1)->getMessage());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The option "message" with value 10 is expected to be of type "null" or "string", but is of type "integer".
     */
    public function testValidateWithWrongCustomSingleMessageType(): void
    {
        $this->validator->validateRequest($this->request, [
            'username' => [
                'rules' => V::length(8)->alnum(),
                'message' => 10
            ]
        ]);
    }

    public function testValidateWithCustomSingleMessage(): void
    {
        $errors = $this->validator->validateRequest($this->request, [
            'username' => [
                'rules' => V::length(8)->alnum(),
                'message' => 'Bad username.',
                'messages' => [
                    'length' => 'Too short!'
                ]
            ],
            'password' => [
                'rules' => V::length(8),
                'messages' => [
                    'length' => 'Too short!'
                ]
            ]
        ]);

        $this->assertSame(2, $errors->count());
        $this->assertSame('username', $errors->get(0)->getPath());
        $this->assertSame('Bad username.', $errors->get(0)->getMessage());
        $this->assertSame('password', $errors->get(1)->getPath());
        $this->assertSame('Too short!', $errors->get(1)->getMessage());
    }

    public function testSetDefaultMessage(): void
    {
        $this->assertEquals([], $this->validator->getDefaultMessages());

        $this->validator->setDefaultMessage('length', 'Too short!');

        $this->assertEquals(['length' => 'Too short!'], $this->validator->getDefaultMessages());
    }
}
