<?php

namespace Awurth\SlimValidation\Tests;

use Awurth\SlimValidation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as V;
use Slim\Http\Environment;
use Slim\Http\Request;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Request
     */
    protected $request;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'username=a_wurth&password=1234'
        ]));

        $this->validator = new Validator();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateWithoutRules()
    {
        $this->validator->validate($this->request, [
            'username'
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateWithWrongOptionsType()
    {
        $this->validator->validate($this->request, [
            'username' => null
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidateWithWrongRulesType()
    {
        $this->validator->validate($this->request, [
            'username' => [
                'rules' => null
            ]
        ]);
    }

    public function testValidate()
    {
        $this->validator->validate($this->request, [
            'username' => V::length(6)
        ]);

        $this->assertEquals(['username' => 'a_wurth'], $this->validator->getValues());
        $this->assertEquals('a_wurth', $this->validator->getValue('username'));
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

    public function testValidateWithCustomDefaultMessage()
    {
        $this->validator->setDefaultMessages([
            'length' => 'Too short!'
        ]);

        $this->validator->validate($this->request, [
            'username' => V::length(8)
        ]);

        $this->assertEquals(['username' => 'a_wurth'], $this->validator->getValues());
        $this->assertEquals('a_wurth', $this->validator->getValue('username'));
        $this->assertFalse($this->validator->isValid());
        $this->assertEquals([
            'username' => [
                'length' => 'Too short!'
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected custom message to be of type string, integer given
     */
    public function testValidateWithWrongCustomSingleMessageType()
    {
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

    public function testSetErrorStorageMode()
    {
        $this->assertTrue($this->validator->getShowValidationRules());

        $this->validator->setShowValidationRules(false);

        $this->assertFalse($this->validator->getShowValidationRules());
    }
}
