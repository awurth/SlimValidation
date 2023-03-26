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

use Awurth\Validator\StatefulValidator;
use Awurth\Validator\Twig\LegacyValidatorExtension;
use Awurth\Validator\ValidationFailure;
use Awurth\Validator\ValidationFailureCollection;
use Awurth\Validator\ValidationFailureCollectionInterface;
use PHPUnit\Framework\TestCase;

class LegacyValidatorExtensionTest extends TestCase
{
    public function testGetError(): void
    {
        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure('first message', null, 'first'),
            new ValidationFailure('second message', null, 'second'),
        ]));

        self::assertSame('first message', $extension->getError('first'));
        self::assertSame('second message', $extension->getError('second'));

        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure('first message of first property', null, 'first'),
            new ValidationFailure('first message of second property', null, 'second'),
            new ValidationFailure('second message of second property', null, 'second'),
            new ValidationFailure('second message of first property', null, 'first'),
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
            new ValidationFailure('first', null, 'first'),
            new ValidationFailure('second', null, 'second'),
            new ValidationFailure('third', null, 'third'),
            new ValidationFailure('fourth', null, 'third'),
        ]));

        self::assertSame(['first', 'second', 'third', 'fourth'], $extension->getErrors());
        self::assertSame(['third', 'fourth'], $extension->getErrors('third'));
    }

    public function testHasError(): void
    {
        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure('message', null, 'property'),
        ]));

        self::assertTrue($extension->hasError('property'));
        self::assertFalse($extension->hasError('missing'));
    }

    public function testHasErrors(): void
    {
        $extension = self::createExtension();

        self::assertFalse($extension->hasErrors());

        $extension = self::createExtension(new ValidationFailureCollection([
            new ValidationFailure('message', null, 'property'),
        ]));

        self::assertTrue($extension->hasErrors());
    }

    private static function createExtension(ValidationFailureCollectionInterface $failures = new ValidationFailureCollection()): LegacyValidatorExtension
    {
        $validator = StatefulValidator::create();
        $validator->getFailures()->addAll($failures);

        return new LegacyValidatorExtension($validator);
    }
}
