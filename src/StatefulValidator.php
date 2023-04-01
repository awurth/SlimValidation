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

namespace Awurth\Validator;

use Awurth\Validator\Assertion\AsserterInterface;
use Awurth\Validator\Failure\ValidationFailureCollectionFactory;
use Awurth\Validator\Failure\ValidationFailureCollectionFactoryInterface;
use Awurth\Validator\Failure\ValidationFailureCollectionInterface;
use Respect\Validation\Validatable;

final class StatefulValidator implements StatefulValidatorInterface
{
    private ValidationFailureCollectionInterface $failures;

    public function __construct(
        private readonly ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory,
        private readonly ValidatorInterface $validator,
    ) {
        $this->failures = $this->validationFailureCollectionFactory->create();
    }

    public static function create(?AsserterInterface $asserter = null): self
    {
        return new self(
            new ValidationFailureCollectionFactory(),
            Validator::create($asserter),
        );
    }

    public function validate(mixed $subject, Validatable|array $rules, array $messages = [], mixed $context = null): ValidationFailureCollectionInterface
    {
        $failures = $this->validator->validate($subject, $rules, $messages, $context);

        $this->failures->addAll($failures);

        return $this->failures;
    }

    public function getFailures(): ValidationFailureCollectionInterface
    {
        return $this->failures;
    }
}
