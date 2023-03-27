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

use Respect\Validation\Exceptions\NestedValidationException;

final class Asserter implements AsserterInterface
{
    /**
     * @param array<string, string> $messages
     */
    public function __construct(
        private readonly ValidationFailureCollectionFactoryInterface $validationFailureCollectionFactory,
        private readonly ValidationFailureFactoryInterface $validationFailureFactory,
        private readonly array $messages = [],
    ) {
    }

    /**
     * @param array<string, string> $messages
     */
    public static function create(array $messages = []): self
    {
        return new self(new ValidationFailureCollectionFactory(), new ValidationFailureFactory(), $messages);
    }

    public function assert(mixed $subject, ValidationInterface $validation, array $messages = []): ValidationFailureCollectionInterface
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
}
