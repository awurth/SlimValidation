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

use Awurth\Validator\Exception\InvalidPropertyOptionsException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validatable;

/**
 * The Validator.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class Validator implements ValidatorInterface
{
    public function __construct(
        private readonly ValidationFactoryInterface $validationFactory,
        private readonly ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory,
        private readonly AsserterInterface $asserter,
    ) {
    }

    /**
     * @param array<string, string> $messages
     */
    public static function create(
        ?AsserterInterface $asserter = null,
        array $messages = [],
    ): self {
        $validationFailureCollectionFactory = new ValidationFailureCollectionFactory();

        return new self(
            new ValidationFactory(),
            $validationFailureCollectionFactory,
            $asserter ?? new Asserter($validationFailureCollectionFactory, new ValidationFailureFactory(), $messages),
        );
    }

    public function validate(mixed $subject, Validatable|array $rules, array $messages = []): ValidationFailureCollectionInterface
    {
        if ($rules instanceof Validatable) {
            return $this->asserter->assert($subject, $this->validationFactory->create(['rules' => $rules]), $messages);
        }

        if (!$subject instanceof Request && !\is_object($subject) && !\is_array($subject)) {
            return $this->asserter->assert($subject, $this->validationFactory->create($rules), $messages);
        }

        $failures = $this->validationFailureCollectionFactory->create();
        foreach ($rules as $property => $options) {
            if ($options instanceof Validatable) {
                $options = ['rules' => $options];
            } elseif (!\is_array($options)) {
                throw new InvalidPropertyOptionsException(\sprintf('Expected an array or an instance of "%s", "%s" given', Validatable::class, \get_debug_type($options)));
            }

            $validation = $this->validationFactory->create($options, $property);
            $value = $this->getValue($subject, $property, $validation->getDefault());

            $failures->addAll($this->asserter->assert($value, $validation, $messages));
        }

        return $failures;
    }

    private function getValue(mixed $subject, string $property, mixed $default = null): mixed
    {
        if (\is_array($subject)) {
            return \array_key_exists($property, $subject) ? $subject[$property] : $default;
        }

        if ($subject instanceof Request) {
            return RequestParameterAccessor::getValue($subject, $property, $default);
        }

        if (\is_object($subject)) {
            return ObjectPropertyAccessor::getValue($subject, $property, $default);
        }

        throw new \InvalidArgumentException(\sprintf('The subject must be of type "array", "object" or "%s", "%s" given', Request::class, \get_class($subject)));
    }
}
