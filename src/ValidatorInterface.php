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

use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validatable;

/**
 * Validator Interface.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidatorInterface
{
    /**
     * Validates request parameters, an array or an objects properties.
     *
     * @param Request|mixed       $input
     * @param Validatable[]|array $rules
     * @param string|null         $group
     * @param string[]            $messages
     * @param mixed|null          $default
     */
    public function validate($input, array $rules, ?string $group = null, array $messages = [], $default = null);

    /**
     * Tells if there is no error.
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Gets one error.
     *
     * @param string          $key
     * @param string|int|null $index
     * @param string|null     $group
     *
     * @return string
     */
    public function getError(string $key, $index = null, $group = null): string;

    /**
     * Gets multiple errors.
     *
     * @param string|null $key
     * @param string|null $group
     *
     * @return string[]
     */
    public function getErrors(?string $key = null, ?string $group = null): array;

    /**
     * Gets a value from the validated data.
     *
     * @param string      $key
     * @param string|null $group
     *
     * @return mixed
     */
    public function getValue(string $key, ?string $group = null);

    /**
     * Gets the validated data.
     *
     * @param string|null $group
     *
     * @return array
     */
    public function getValues(?string $group = null): array;
}
