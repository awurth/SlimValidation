<?php

namespace Awurth\Validator;

use Respect\Validation\Validatable;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Validation
{
    private static ?OptionsResolver $optionsResolver = null;

    public function __construct(
        private readonly Validatable $rules,
        private readonly ?string $property = null,
        private readonly mixed $default = null,
        private readonly ?string $message = null,
        private readonly array $messages = []
    ) {
    }

    public static function create(array $options, ?string $property = null, mixed $default = null): self
    {
        $options = self::getOptionsResolver()->resolve($options);

        return new self(
            $options['rules'],
            $property,
            $options['default'] ?? $default,
            $options['message'],
            $options['messages']
        );
    }

    public function getRules(): Validatable
    {
        return $this->rules;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private static function getOptionsResolver(): OptionsResolver
    {
        if (null === self::$optionsResolver) {
            self::$optionsResolver = (new OptionsResolver())
                ->setDefaults([
                    'message' => null,
                    'messages' => [],
                ])
                ->setRequired('rules')
                ->setAllowedTypes('rules', Validatable::class)
                ->setAllowedTypes('message', ['null', 'string'])
                ->setAllowedTypes('messages', 'string[]')
            ;
        }

        return self::$optionsResolver;
    }
}
