<?php

namespace Awurth\SlimValidation\Tests;

use Awurth\SlimValidation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as V;
use Slim\Http\Environment;
use Slim\Http\Request;
use TypeError;

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

    public function testValidateWithoutRules()
    {
        $this->expectException(TypeError::class);
        $this->validator->validate($this->request, [
            'username'
        ]);
    }

    public function testValidateWithOptionsWrongType()
    {
        $this->expectException(TypeError::class);
        $this->validator->validate($this->request, [
            'username' => null
        ]);
    }

    public function testValidateWithRulesWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->validator->validate($this->request, [
            'username' => [
                'rules' => null
            ]
        ]);
    }

    public function testRequest()
    {
        $this->validator->request($this->request, [
            'username' => V::length(6)
        ]);

        $this->assertEquals(['username' => 'a_wurth'], $this->validator->getValues());
        $this->assertEquals('a_wurth', $this->validator->getValue('username'));
        $this->assertTrue($this->validator->isValid());
    }

    public function testRequestWithGroup()
    {
        $this->validator->request($this->request, [
            'username' => V::length(6)
        ], 'user');

        $this->assertEquals([
            'user' => [
                'username' => 'a_wurth'
            ]
        ], $this->validator->getValues());
        $this->assertEquals('a_wurth', $this->validator->getValue('username', 'user'));
        $this->assertTrue($this->validator->isValid());
    }

    public function testArray()
    {
        $this->validator->array($this->array, [
            'username' => V::notBlank(),
            'password' => V::notBlank()
        ]);

        $this->assertEquals(['username' => 'a_wurth', 'password' => '1234'], $this->validator->getValues());
        $this->assertTrue($this->validator->isValid());
    }

    public function testObject()
    {
        $this->validator->object($this->object, [
            'privateProperty' => V::notBlank(),
            'protectedProperty' => V::notBlank(),
            'publicProperty' => V::notBlank()
        ]);

        $this->assertEquals([
            'privateProperty' => 'private',
            'protectedProperty' => 'protected',
            'publicProperty' => 'public',
        ], $this->validator->getValues());
        $this->assertTrue($this->validator->isValid());
    }

    public function testValue()
    {
        $this->validator->value(2017, V::numeric()->between(2010, 2020), 'year');

        $this->assertEquals(['year' => 2017], $this->validator->getValues());
        $this->assertTrue($this->validator->isValid());
    }

    public function testValidateWithErrors()
    {
        $this->validator->validate($this->request, [
            'username' => V::length(8)
        ]);

        $this->assertEquals(['username' => 'a_wurth'], $this->validator->getValues());
        $this->assertEquals('a_wurth', $this->validator->getValue('username'));
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'length' => '"a_wurth" must have a length greater than 8'
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithIndexedErrors()
    {
        $this->validator->setShowValidationRules(false);
        $this->validator->validate($this->request, [
            'username' => V::length(8)
        ]);

        $this->assertEquals(['username' => 'a_wurth'], $this->validator->getValues());
        $this->assertEquals('a_wurth', $this->validator->getValue('username'));
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                '"a_wurth" must have a length greater than 8'
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithGroupedErrors()
    {
        $this->validator->validate($this->request, [
            'username' => V::length(8)
        ], 'user');

        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'user' => [
                'username' => [
                    'length' => '"a_wurth" must have a length greater than 8'
                ]
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomDefaultMessage()
    {
        $this->validator->setDefaultMessages([
            'length' => 'Too short!'
        ]);

        $this->validator->validate($this->request, [
            'username' => V::length(8)
        ]);

        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'length' => 'Too short!'
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomDefaultMessageAndGroup()
    {
        $this->validator->setDefaultMessages([
            'length' => 'Too short!'
        ]);

        $this->validator->validate($this->request, [
            'username' => V::length(8)
        ], 'user');

        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'user' => [
                'username' => [
                    'length' => 'Too short!'
                ]
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomGlobalMessages()
    {
        $this->validator->validate($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)
        ], null, [
            'length' => 'Too short!'
        ]);

        $this->assertEquals(['username' => 'a_wurth', 'password' => '1234'], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'length' => 'Too short!'
            ],
            'password' => [
                'length' => 'Too short!'
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomGlobalMessagesAndGroup()
    {
        $this->validator->validate($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)
        ], 'user', [
            'length' => 'Too short!'
        ]);

        $this->assertEquals([
            'user' => [
                'username' => 'a_wurth',
                'password' => '1234'
            ]
        ], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'user' => [
                'username' => [
                    'length' => 'Too short!'
                ],
                'password' => [
                    'length' => 'Too short!'
                ]
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomDefaultAndGlobalMessages()
    {
        $this->validator->setDefaultMessage('length', 'Too short!');

        $this->validator->validate($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)->alpha()
        ], null, [
            'alpha' => 'Only letters are allowed'
        ]);

        $this->assertEquals(['username' => 'a_wurth', 'password' => '1234'], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'length' => 'Too short!'
            ],
            'password' => [
                'length' => 'Too short!',
                'alpha' => 'Only letters are allowed'
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomDefaultAndGlobalMessagesAndGroup()
    {
        $this->validator->setDefaultMessage('length', 'Too short!');

        $this->validator->validate($this->request, [
            'username' => V::length(8),
            'password' => V::length(8)->alpha()
        ], 'user', [
            'alpha' => 'Only letters are allowed'
        ]);

        $this->assertEquals([
            'user' => [
                'username' => 'a_wurth',
                'password' => '1234'
            ]
        ], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'user' => [
                'username' => [
                    'length' => 'Too short!'
                ],
                'password' => [
                    'length' => 'Too short!',
                    'alpha' => 'Only letters are allowed'
                ]
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomIndividualMessage()
    {
        $this->validator->validate($this->request, [
            'username' => [
                'rules' => V::length(8),
                'messages' => [
                    'length' => 'Too short!'
                ]
            ],
            'password' => V::length(8)
        ]);

        $this->assertEquals(['username' => 'a_wurth', 'password' => '1234'], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'length' => 'Too short!'
            ],
            'password' => [
                'length' => '"1234" must have a length greater than 8'
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithCustomIndividualMessageAndGroup()
    {
        $this->validator->validate($this->request, [
            'username' => [
                'rules' => V::length(8),
                'messages' => [
                    'length' => 'Too short!'
                ]
            ],
            'password' => V::length(8)
        ], 'user');

        $this->assertEquals([
            'user' => [
                'username' => 'a_wurth',
                'password' => '1234'
            ]
        ], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'user' => [
                'username' => [
                    'length' => 'Too short!'
                ],
                'password' => [
                    'length' => '"1234" must have a length greater than 8'
                ]
            ]
        ], $this->validator->getErrors());
    }

    public function testValidateWithWrongCustomSingleMessageType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected custom message to be of type string, integer given');
        $this->validator->validate($this->request, [
            'username' => [
                'rules' => V::length(8)->alnum(),
                'message' => 10
            ]
        ]);
    }

    public function testValidateWithCustomSingleMessage()
    {
        $this->validator->validate($this->request, [
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

        $this->assertEquals(['username' => 'a_wurth', 'password' => '1234'], $this->validator->getValues());
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'Bad username.'
            ],
            'password' => [
                'length' => 'Too short!'
            ]
        ], $this->validator->getErrors());
    }

    public function testIsValidWithErrors()
    {
        $this->validator->setErrors(['error']);

        $this->assertFalse($this->validator->isValid());
    }

    public function testIsValidWithoutErrors()
    {
        $this->validator->removeErrors();

        $this->assertTrue($this->validator->isValid());
    }

    public function testAddError()
    {
        $this->validator->addError('param', 'message');

        $this->assertEquals([
            'param' => [
                'message'
            ]
        ], $this->validator->getErrors());
    }

    public function testGetFirstError()
    {
        $this->assertEquals('', $this->validator->getFirstError('username'));

        $this->validator->setErrors([
            'param' => [
                'notBlank' => 'Required'
            ],
            'username' => [
                'alnum' => 'Only letters and numbers are allowed',
                'length' => 'Too short!'
            ]
        ]);

        $this->assertEquals('Only letters and numbers are allowed', $this->validator->getFirstError('username'));

        $this->validator->setErrors([
            'param' => [
                'Required'
            ],
            'username' => [
                'This field is required',
                'Only letters and numbers are allowed'
            ]
        ]);

        $this->assertEquals('This field is required', $this->validator->getFirstError('username'));
    }

    public function testGetErrors()
    {
        $this->assertEquals([], $this->validator->getErrors('username'));

        $this->validator->setErrors([
            'param' => [
                'Required'
            ],
            'username' => [
                'This field is required',
                'Only letters and numbers are allowed'
            ]
        ]);

        $this->assertEquals([
            'This field is required',
            'Only letters and numbers are allowed'
        ], $this->validator->getErrors('username'));
    }

    public function testGetError()
    {
        $this->assertEquals('', $this->validator->getError('username', 'length'));

        $this->validator->setErrors([
            'username' => [
                'alnum' => 'Only letters and numbers are allowed',
                'length' => 'Too short!'
            ]
        ]);

        $this->assertEquals('Too short!', $this->validator->getError('username', 'length'));
    }

    public function testSetValues()
    {
        $this->assertEquals([], $this->validator->getValues());

        $this->validator->setValues([
            'username' => 'awurth',
            'password' => 'pass'
        ]);

        $this->assertEquals([
            'username' => 'awurth',
            'password' => 'pass'
        ], $this->validator->getValues());
    }

    public function testSetDefaultMessage()
    {
        $this->assertEquals([], $this->validator->getDefaultMessages());

        $this->validator->setDefaultMessage('length', 'Too short!');

        $this->assertEquals([
            'length' => 'Too short!'
        ], $this->validator->getDefaultMessages());
    }

    public function testSetErrors()
    {
        $this->assertEquals([], $this->validator->getErrors());

        $this->validator->setErrors([
            'notBlank' => 'Required',
            'length' => 'Too short!'
        ], 'username');

        $this->assertEquals([
            'username' => [
                'notBlank' => 'Required',
                'length' => 'Too short!'
            ]
        ], $this->validator->getErrors());
    }

    public function testSetShowValidationRules()
    {
        $this->assertTrue($this->validator->getShowValidationRules());

        $this->validator->setShowValidationRules(false);

        $this->assertFalse($this->validator->getShowValidationRules());
    }

    public function testValidateInvalidSearcher()
    {
        $this->validator->value('FR', [
            'rules' => V::subdivisionCode('US')
        ], 'subdivision');

        $this->assertFalse($this->validator->isValid());
    }
}
