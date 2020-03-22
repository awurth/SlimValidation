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

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use ReflectionClass;
use ReflectionException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AllOf;
use Respect\Validation\Rules\AbstractWrapper;
use Slim\Interfaces\RouteInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
class Validator
{
    /**
     * The default error messages for the given rules.
     *
     * @var string[]
     */
    private $defaultMessages;

    /**
     * The list of validation errors.
     *
     * @var ValidationErrorList
     */
    private $errors;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(array $defaultMessages = [])
    {
        $this->defaultMessages = $defaultMessages;
        $this->errors = new ValidationErrorList();
    }

    /**
     * Validates an array with the given rules.
     *
     * @param array         $array
     * @param AllOf[]|array $rules
     * @param string[]      $messages
     * @param mixed|null    $default
     *
     * @return self
     */
    public function validateArray(array $array, array $rules, array $messages = [], $default = null): self
    {
        foreach ($rules as $key => $options) {
            $this->validateInput(
                $array[$key] ?? $default,
                new Configuration($options, $key, $default),
                $messages
            );
        }

        return $this;
    }

    /**
     * Validates an objects properties with the given rules.
     *
     * @param object        $object
     * @param AllOf[]|array $rules
     * @param string[]      $messages
     * @param mixed|null    $default
     *
     * @return self
     */
    public function validateObject($object, array $rules, array $messages = [], $default = null): self
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The first argument should be an object');
        }

        foreach ($rules as $property => $options) {
            $input = $this->getPropertyAccessor()->isReadable($object, $property)
                ? $this->getPropertyAccessor()->getValue($object, $property)
                : $default;

            $this->validateInput($input, new Configuration($options, $property, $default), $messages);
        }

        return $this;
    }

    /**
     * Validates request parameters with the given rules.
     *
     * @param Request       $request
     * @param AllOf[]|array $rules
     * @param string[]      $messages
     * @param mixed|null    $default
     *
     * @return self
     */
    public function validateRequest(Request $request, array $rules, array $messages = [], $default = null): self
    {
        foreach ($rules as $param => $options) {
            $this->validateInput(
                $this->getRequestParam($request, $param, $default),
                new Configuration($options, $param, $default),
                $messages
            );
        }

        return $this;
    }

    /**
     * Validates a single value with the given rules.
     *
     * @param mixed       $input
     * @param AllOf|array $rules
     * @param string      $key
     * @param string[]    $messages
     *
     * @return self
     */
    public function validate($input, $rules, string $key, array $messages = []): self
    {
        return $this->validateInput($input, new Configuration($rules, $key), $messages);
    }

    public function getDefaultMessage(string $key): string
    {
        return $this->defaultMessages[$key] ?? '';
    }

    public function getDefaultMessages(): array
    {
        return $this->defaultMessages;
    }

    public function getErrors(): ValidationErrorList
    {
        return $this->errors;
    }

    public function setDefaultMessage(string $rule, string $message): self
    {
        $this->defaultMessages[$rule] = $message;

        return $this;
    }

    public function setDefaultMessages(array $messages): self
    {
        $this->defaultMessages = $messages;

        return $this;
    }

    /**
     * Fetches a request parameter's value from the body or query string (in that order).
     *
     * @param Request     $request
     * @param string      $key
     * @param string|null $default
     *
     * @return mixed
     */
    protected function getRequestParam(Request $request, $key, $default = null)
    {
        $postParams = $request->getParsedBody();
        $getParams = $request->getQueryParams();
        $route = $request->getAttribute('route');

        $routeParams = [];
        if ($route instanceof RouteInterface) {
            $routeParams = $route->getArguments();
        }

        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        } elseif (isset($routeParams[$key])) {
            $result = $routeParams[$key];
        } elseif (isset($_FILES[$key])) {
            $result = $_FILES[$key];
        }

        return $result;
    }

    /**
     * Gets the name of all rules of a group of rules.
     *
     * @param AllOf $rules
     *
     * @return string[]
     */
    protected function getRulesNames(AllOf $rules): array
    {
        $rulesNames = [];
        foreach ($rules->getRules() as $rule) {
            try {
                if ($rule instanceof AbstractWrapper) {
                    $rulesNames = array_merge($rulesNames, $this->getRulesNames($rule->getValidatable()));
                } else {
                    $rulesNames[] = lcfirst((new ReflectionClass($rule))->getShortName());
                }
            } catch (ReflectionException $e) {
            }
        }

        return $rulesNames;
    }

    protected function handleValidationException(NestedValidationException $e, Configuration $config, array $messages = []): void
    {
        if ($config->hasMessage()) {
            $this->errors->add(new ValidationError($config->getKey(), $config->getMessage()));
        } else {
            foreach ($this->findErrorMessages($e, $config, $messages) as $ruleName => $message) {
                $this->errors->add(
                    (new ValidationError($config->getKey(), $message))->setRule($ruleName)
                );
            }
        }
    }

    protected function findErrorMessages(NestedValidationException $e, Configuration $config, array $messages = []): array
    {
        $errors = [
            $e->findMessages($this->getRulesNames($config->getValidationRules()))
        ];

        // If default messages are defined
        if (!empty($this->defaultMessages)) {
            $errors[] = $e->findMessages($this->defaultMessages);
        }

        // If global messages are defined
        if (!empty($messages)) {
            $errors[] = $e->findMessages($messages);
        }

        // If individual messages are defined
        if ($config->hasMessages()) {
            $errors[] = $e->findMessages($config->getMessages());
        }

        return array_filter(array_merge(...$errors));
    }

    protected function validateInput($input, Configuration $config, array $messages = []): self
    {
        try {
            $config->getValidationRules()->assert($input);
        } catch (NestedValidationException $e) {
            $this->handleValidationException($e, $config, $messages);
        }

        return $this;
    }

    private function getPropertyAccessor(): PropertyAccessor
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
