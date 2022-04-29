<?php

/*
 * This file is part of the Awurth Validator package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\Validator;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

/**
 * The Validator.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class Validator
{
    public function __construct(
        private readonly ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory,
        private readonly ValidationFailureFactoryInterface $validationFailureFactory
    ) {
    }

    public function validate(mixed $subject, Validatable|array $rules, array $messages = []): ValidationFailureCollectionInterface
    {
        if ($rules instanceof Validatable) {
            return $this->assert($subject, Validation::create(['rules' => $rules]), $messages);
        }

        if (!$subject instanceof Request && !is_object($subject) && !is_array($subject)) {
            return $this->assert($subject, Validation::create($rules), $messages);
        }

        $failures = $this->validationFailureCollectionFactory->create();
        foreach ($rules as $property => $options) {
            $value = $this->getValue($subject, $property);
            $failures->addAll(
                $this->assert($value, Validation::create($options, $property), $messages)
            );
        }

        return $failures;
    }

    private function assert(mixed $subject, Validation $validation, array $messages = []): ValidationFailureCollectionInterface
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

    private function extractMessagesFromException(NestedValidationException $exception, Validation $validation, array $messages = []): array
    {
        $definedMessages = array_replace(/*$this->defaultMessages, */ $messages, $validation->getMessages());

        $errors = [];
        foreach ($exception->getMessages($definedMessages) as $name => $error) {
            if (is_array($error)) {
                $errors = [...$errors, ...$error];
            } else {
                $errors[$name] = $error;
            }
        }

        return $errors;
    }

    private function getValue(mixed $subject, string $property, mixed $default = null): mixed
    {
        if (is_array($subject)) {
            return $subject[$property] ?? $default;
        }

        if ($subject instanceof Request) {
            return RequestParameterAccessor::getValue($subject, $property, $default);
        }

        if (is_object($subject)) {
            return ObjectPropertyAccessor::getValue($subject, $property, $default);
        }

        throw new InvalidArgumentException(
            sprintf(
                'The subject must be of type "array", "object" or "%s", "%s" given',
                Request::class,
                get_class($subject)
            )
        );
    }
}
