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

use Awurth\Validator\Assertion\Asserter;
use Awurth\Validator\Assertion\AsserterInterface;
use Awurth\Validator\Exception\InvalidPropertyOptionsException;
use Awurth\Validator\Failure\ValidationFailureCollectionFactory;
use Awurth\Validator\Failure\ValidationFailureCollectionInterface;
use Awurth\Validator\Failure\ValidationFailureFactory;
use Awurth\Validator\ValueReader\ValueReaderRegistry;
use Awurth\Validator\ValueReader\ValueReaderRegistryInterface;
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
        private readonly AsserterInterface $asserter,
        private readonly ValueReaderRegistryInterface $valueReaderRegistry,
    ) {
    }

    /**
     * @param array<string, string> $messages
     */
    public static function create(
        ?AsserterInterface $asserter = null,
        array $messages = [],
    ): self {
        return new self(
            new ValidationFactory(),
            $asserter ?? new Asserter(new ValidationFailureCollectionFactory(), new ValidationFailureFactory(), $messages),
            ValueReaderRegistry::create(),
        );
    }

    public function validate(mixed $subject, Validatable|array $rules, array $messages = [], mixed $context = null): ValidationFailureCollectionInterface
    {
        if ($rules instanceof Validatable) {
            return $this->asserter->assert($subject, $this->validationFactory->create([
                'rules' => $rules,
                'globalMessages' => $messages,
                'context' => $context,
            ]), $messages);
        }

        if (!$subject instanceof Request && !\is_object($subject) && !\is_array($subject)) {
            $rules['globalMessages'] = $messages;
            $rules['context'] = $context;

            return $this->asserter->assert($subject, $this->validationFactory->create($rules), $messages);
        }

        if ([] === $rules) {
            throw new \InvalidArgumentException('Rules cannot be empty');
        }

        $valueReader = $this->valueReaderRegistry->getValueReaderFor($subject);

        $failures = null;
        foreach ($rules as $property => $options) {
            if ($options instanceof Validatable) {
                $options = ['rules' => $options];
            } elseif (!\is_array($options)) {
                throw new InvalidPropertyOptionsException(\sprintf('Expected an array or an instance of "%s", "%s" given', Validatable::class, \get_debug_type($options)));
            }

            $options['globalMessages'] = $messages;
            $options['context'] = $context;

            $validation = $this->validationFactory->create($options, $property);
            $value = $valueReader->getValue($subject, $property, $validation->getDefault());

            if (!$failures instanceof ValidationFailureCollectionInterface) {
                $failures = $this->asserter->assert($value, $validation, $messages);
                continue;
            }

            $failures->addAll($this->asserter->assert($value, $validation, $messages));
        }

        return $failures;
    }
}
