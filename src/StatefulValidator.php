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

    public static function create(
        ?ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory = null,
        ?ValidatorInterface $validator = null,
    ): self {
        return new self(
            $validationFailureCollectionFactory ?? new ValidationFailureCollectionFactory(),
            $validator ?? Validator::create(),
        );
    }

    public function validate(mixed $subject, Validatable|array $rules, array $messages = []): ValidationFailureCollectionInterface
    {
        $this->failures->addAll($this->validator->validate($subject, $rules, $messages));

        return $this->failures;
    }

    public function getFailures(): ValidationFailureCollectionInterface
    {
        return $this->failures;
    }
}
