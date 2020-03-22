<?php

/*
 * This file is part of the awurth/slim-validation package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\SlimValidation;

use Respect\Validation\Validatable as RespectValidatable;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
class ValidatableFactory
{
    private static $optionsResolver;

    public static function create(string $path, $options, $default = null): Validatable
    {
        if ($options instanceof RespectValidatable) {
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
                ->setAllowedTypes('rules', RespectValidatable::class)
                ->setAllowedTypes('message', ['null', 'string'])
                ->setAllowedTypes('messages', 'string[]');
        }

        return self::$optionsResolver;
    }
}
