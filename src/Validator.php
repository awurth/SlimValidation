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
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

/**
 * The Validator.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class Validator implements ValidatorInterface
{
    /**
     * @param array<string, string> $messages
     */
    public function __construct(
        private readonly ValidationFactoryInterface $validationFactory,
        private readonly ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory,
        private readonly ValidationFailureFactoryInterface $validationFailureFactory,
        private readonly array $messages = [],
    ) {
    }

    /**
     * @param array<string, string> $messages
     */
    public static function create(
        ?ValidationFactoryInterface $validationFactory = null,
        ?ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory = null,
        ?ValidationFailureFactoryInterface $validationFailureFactory = null,
        array $messages = [],
    ): self {
        return new self(
            $validationFactory ?? new ValidationFactory(),
            $validationFailureCollectionFactory ?? new ValidationFailureCollectionFactory(),
            $validationFailureFactory ?? new ValidationFailureFactory(),
            $messages,
        );
    }

    public function validate(mixed $subject, Validatable|array $rules, array $messages = []): ValidationFailureCollectionInterface
    {
        if ($rules instanceof Validatable) {
            return $this->assert($subject, $this->validationFactory->create(['rules' => $rules]), $messages);
        }

        if (!$subject instanceof Request && !\is_object($subject) && !\is_array($subject)) {
            return $this->assert($subject, $this->validationFactory->create($rules), $messages);
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

            $failures->addAll($this->assert($value, $validation, $messages));
        }

        return $failures;
    }

    /**
     * @param array<string, string> $messages
     */
    private function assert(mixed $subject, ValidationInterface $validation, array $messages = []): ValidationFailureCollectionInterface
    {
        $failures = $this->validationFailureCollectionFactory->create();

        try {
            $validation->getRules()->assert($subject);
        } catch (NestedValidationException $exception) {
            if ($message = $validation->getMessage()) {
                $failures->add(
                    $this->validationFailureFactory->create($message, $subject, $validation->getProperty())
                );

                return $failures;
            }

            $exceptionMessages = $this->extractMessagesFromException($exception, $validation, $messages);
            foreach ($exceptionMessages as $ruleName => $message) {
                $failures->add(
                    $this->validationFailureFactory->create($message, $subject, $validation->getProperty(), $ruleName)
                );
            }
        }

        return $failures;
    }

    /**
     * @param array<string, string> $messages
     *
     * @return array<string, string>
     */
    private function extractMessagesFromException(NestedValidationException $exception, ValidationInterface $validation, array $messages = []): array
    {
        $definedMessages = \array_replace($this->messages, $messages, $validation->getMessages());

        $errors = [];
        foreach ($exception->getMessages($definedMessages) as $name => $error) {
            if (\is_array($error)) {
                $errors = [...$errors, ...$error];
            } else {
                $errors[$name] = $error;
            }
        }

        return $errors;
    }

    private function getValue(mixed $subject, string $property, mixed $default = null): mixed
    {
        if (\is_array($subject)) {
            return $subject[$property] ?? $default;
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
