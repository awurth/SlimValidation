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

use Respect\Validation\Validatable;

/**
 * Contains validation rules and other data used to handle validation failures.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
interface ValidationInterface
{
    public function getRules(): Validatable;

    /**
     * Gets the object property, array key or request parameter.
     */
    public function getProperty(): ?string;

    public function getDefault(): mixed;

    public function getMessage(): ?string;

    public function getMessages(): array;

    public function getGlobalMessages(): array;

    public function getContext(): mixed;
}
