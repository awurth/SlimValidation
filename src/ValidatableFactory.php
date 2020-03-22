<?php

namespace Awurth\SlimValidation;

use Respect\Validation\Rules\AllOf;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidatableFactory
{
    private static $optionsResolver;

    public static function create(string $path, $options, $default = null): Validatable
    {
        if ($options instanceof AllOf) {
            $options = ['rules' => $options];
        }

        $options = self::getOptionsResolver()->resolve($options);

        return (new Validatable($path, $options['rules']))
            ->setDefault($options['default'] ?? $default)
            ->setMessage($options['message'])
            ->setMessages($options['messages']);
    }

    private static function getOptionsResolver(): OptionsResolver
    {
        if (null === self::$optionsResolver) {
            self::$optionsResolver = (new OptionsResolver())
                ->setDefaults([
                    'message' => null,
                    'messages' => []
                ])
                ->setRequired('rules')
                ->setAllowedTypes('rules', AllOf::class)
                ->setAllowedTypes('message', ['null', 'string'])
                ->setAllowedTypes('messages', 'string[]');
        }

        return self::$optionsResolver;
    }
}
