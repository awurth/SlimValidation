<?php

declare(strict_types=1);

namespace Awurth\Validator;

use Respect\Validation\Validatable;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Handles the creation of a Validation.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class ValidationFactory implements ValidationFactoryInterface
{
    private static ?OptionsResolver $optionsResolver = null;

    public function create(array $options, ?string $property = null, mixed $default = null): ValidationInterface
    {
        $options = self::getOptionsResolver()->resolve($options);

        return new Validation(
            $options['rules'],
            $property,
            $options['default'] ?? $default,
            $options['message'],
            $options['messages']
        );
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
