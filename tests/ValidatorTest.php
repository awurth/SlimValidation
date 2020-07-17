<?php

namespace Awurth\SlimValidation\Tests;

use Awurth\SlimValidation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as V;
use Slim\Http\Environment;
use Slim\Http\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ValidatorTest extends TestCase
{
    /**
     * @var array
     */
    private $array;

    /**
     * @var TestObject
     */
    private $object;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Validator
     */
    private $validator;

    public function setUp(): void
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

    public function testValidateWithoutRules(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->validator->validateRequest($this->request, ['username' => null]);
    }

    public function testValidateWithRulesWrongType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $this->validator->validateRequest($this->request, [
            'username' => [
                'rules' => null
            ]
        ]);
    }

    public function testRequest(): void
    {
        $errors = $this->validator->validateRequest($this->request, ['username' => V::length(6)]);

        self::assertSame(0, $errors->count());
    }

    public function testArray(): void
    {
        $errors = $this->validator->validateArray($this->array, [
            'username' => V::notBlank(),
            'password' => V::notBlank()
        ]);

        self::assertSame(0, $errors->count());
    }

    public function testObject(): void
    {
        $errors = $this->validator->validateObject($this->object, [
            'privateProperty' => V::notBlank(),
            'protectedProperty' => V::notBlank(),
            'publicProperty' => V::notBlank()
        ]);

        self::assertSame(1, $errors->count());
    }

    public function testValue(): void
    {
        $errors = $this->validator->validate(2017, V::numericVal()->between(2010, 2020), 'year');

        self::assertSame(0, $errors->count());
    }

    public function testValidateWithErrors(): void
    {
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8)
        ]);

        self::assertSame(1, $errors->count());

        $error = $errors->get(0);

        self::assertSame('username', $error->getPath());
        self::assertSame('length', $error->getRule());
        self::assertSame('a_wurth', $error->getInvalidValue());
        self::assertSame('"a_wurth" must have a length greater than or equal to 8', $error->getMessage());
    }

    public function testValidateWithCustomDefaultMessage(): void
    {
        $this->validator->setDefaultMessages(['length' => 'Too short!']);
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8)
        ]);

        self::assertSame(1, $errors->count());
        self::assertSame('Too short!', $errors->get(0)->getMessage());
    }

    public function testValidateWithCustomGlobalMessages(): void
    {
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)
        ], ['length' => 'Too short!']);

        self::assertSame(2, $errors->count());
        self::assertSame('Too short!', $errors->get(0)->getMessage());
        self::assertSame('Too short!', $errors->get(1)->getMessage());
    }

    public function testValidateWithCustomDefaultAndGlobalMessages(): void
    {
        $this->validator->setDefaultMessage('length', 'Too short!');
        $errors = $this->validator->validateRequest($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)->alpha()
        ], ['alpha' => 'Only letters are allowed']);

        self::assertSame(3, $errors->count());
        self::assertSame('Too short!', $errors->get(0)->getMessage());
        self::assertSame('Too short!', $errors->get(1)->getMessage());
        self::assertSame('Only letters are allowed', $errors->get(2)->getMessage());
        self::assertSame('alpha', $errors->get(2)->getRule());
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

        self::assertSame(2, $errors->count());
        self::assertSame('username', $errors->get(0)->getPath());
        self::assertSame('Too short!', $errors->get(0)->getMessage());
        self::assertSame('password', $errors->get(1)->getPath());
        self::assertSame('"1234" must have a length greater than or equal to 8', $errors->get(1)->getMessage());
    }

    public function testValidateWithWrongCustomSingleMessageType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The option \"message\" with value 10 is expected to be of type \"null\" or \"string\", but is of type \"int\".");

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

        self::assertSame(2, $errors->count());
        self::assertSame('username', $errors->get(0)->getPath());
        self::assertSame('Bad username.', $errors->get(0)->getMessage());
        self::assertSame('password', $errors->get(1)->getPath());
        self::assertSame('Too short!', $errors->get(1)->getMessage());
    }

    public function testSetDefaultMessage(): void
    {
        self::assertEquals([], $this->validator->getDefaultMessages());

        $this->validator->setDefaultMessage('length', 'Too short!');

        self::assertEquals(['length' => 'Too short!'], $this->validator->getDefaultMessages());
    }
}
