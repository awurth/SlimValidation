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

namespace Awurth\Validator\Tests\Twig;

use Awurth\Validator\Failure\ValidationFailure;
use Awurth\Validator\Failure\ValidationFailureCollection;
use Awurth\Validator\Failure\ValidationFailureCollectionInterface;
use Awurth\Validator\StatefulValidator;
use Awurth\Validator\Tests\NoopDataCollectorAsserter;
use Awurth\Validator\Twig\LegacyValidatorExtension;
use Awurth\Validator\ValidatedValue;
use Awurth\Validator\ValidatedValueCollection;
use Awurth\Validator\ValidatedValueCollectionInterface;
use Awurth\Validator\Validation;
use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as V;

final class LegacyValidatorExtensionTest extends TestCase
{
    public function testGetError(): void
    {
        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'first'), 'first message', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'second'), 'second message', null),
        ]));

        self::assertSame('first message', $extension->getError('first'));
        self::assertSame('second message', $extension->getError('second'));

        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'first'), 'first message of first property', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'second'), 'first message of second property', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'second'), 'second message of second property', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'first'), 'second message of first property', null),
        ]));

        self::assertSame('first message of first property', $extension->getError('first'));
        self::assertSame('second message of first property', $extension->getError('first', 1));
        self::assertNull($extension->getError('first', 2));
        self::assertSame('first message of second property', $extension->getError('second'));
        self::assertSame('second message of second property', $extension->getError('second', 1));
        self::assertNull($extension->getError('second', 2));
    }

    public function testGetErrors(): void
    {
        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'first'), 'first', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'second'), 'second', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'third'), 'third', null),
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'third'), 'fourth', null),
        ]));

        self::assertSame(['first', 'second', 'third', 'fourth'], $extension->getErrors());
        self::assertSame(['third', 'fourth'], $extension->getErrors('third'));
    }

    public function testGetValue(): void
    {
        $extension = self::createExtension(data: new ValidatedValueCollection([
            new ValidatedValue(new Validation(V::alwaysInvalid(), 'property'), 'invalid string'),
        ]));

        self::assertSame('invalid string', $extension->getValue('property'));
        self::assertNull($extension->getValue('prop'));
    }

    public function testHasError(): void
    {
        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'property'), 'message', null),
        ]));

        self::assertTrue($extension->hasError('property'));
        self::assertFalse($extension->hasError('missing'));
    }

    public function testHasErrors(): void
    {
        $extension = self::createExtension();

        self::assertFalse($extension->hasErrors());

        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure(new Validation(V::alwaysInvalid(), 'property'), 'message', null),
        ]));

        self::assertTrue($extension->hasErrors());
    }

    private static function createExtension(
        ValidationFailureCollectionInterface $failures = new ValidationFailureCollection(),
        ValidatedValueCollectionInterface $data = new ValidatedValueCollection(),
    ): LegacyValidatorExtension {
        $asserter = new NoopDataCollectorAsserter($data);
        $validator = StatefulValidator::create($asserter);
        $validator->getFailures()->addAll($failures);

        return new LegacyValidatorExtension($validator, $asserter);
    }
}
