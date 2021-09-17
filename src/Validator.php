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
use ReflectionProperty;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AbstractComposite;
use Respect\Validation\Rules\AbstractWrapper;
use Respect\Validation\Validatable;
use Slim\Interfaces\RouteInterface;

/**
 * Validator.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
class Validator implements ValidatorInterface
{
    /**
     * The default error messages for the given rules.
     *
     * @var string[]
     */
    protected $defaultMessages;

    /**
     * The list of validation errors.
     *
     * @var array
     */
    protected $errors;

    /**
     * Tells whether errors should be stored in an associative array
     * with validation rules as the key, or in an indexed array.
     *
     * @var bool
     */
    protected $showValidationRules;

    /**
     * The validated data.
     *
     * @var array
     */
    protected $values;

    /**
     * Constructor.
     *
     * @param bool     $showValidationRules
     * @param string[] $defaultMessages
     */
    public function __construct(bool $showValidationRules = true, array $defaultMessages = [])
    {
        $this->showValidationRules = $showValidationRules;
        $this->defaultMessages = $defaultMessages;
        $this->errors = [];
        $this->values = [];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Validates an array with the given rules.
     *
     * @param array               $array
     * @param Validatable[]|array $rules
     * @param string|null         $group
     * @param string[]            $messages
     * @param mixed|null          $default
     *
     * @return self
     */
    public function array(array $array, array $rules, ?string $group = null, array $messages = [], $default = null): self
    {
        foreach ($rules as $key => $options) {
            $this->validateInput(
                $array[$key] ?? $default,
                new Configuration($options, $key, $group, $default),
                $messages
            );
        }

        return $this;
    }

    /**
     * Validates an objects properties with the given rules.
     *
     * @param object              $object
     * @param Validatable[]|array $rules
     * @param string|null         $group
     * @param string[]            $messages
     * @param mixed|null          $default
     *
     * @return self
     */
    public function object($object, array $rules, ?string $group = null, array $messages = [], $default = null): self
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The first argument should be an object');
        }

        foreach ($rules as $property => $options) {
            $this->validateInput(
                $this->getPropertyValue($object, $property, $default),
                new Configuration($options, $property, $group, $default),
                $messages
            );
        }

        return $this;
    }

    /**
     * Validates request parameters with the given rules.
     *
     * @param Request             $request
     * @param Validatable[]|array $rules
     * @param string|null         $group
     * @param string[]            $messages
     * @param mixed|null          $default
     *
     * @return self
     */
    public function request(Request $request, array $rules, ?string $group = null, array $messages = [], $default = null): self
    {
        foreach ($rules as $param => $options) {
            $this->validateInput(
                $this->getRequestParam($request, $param, $default),
                new Configuration($options, $param, $group, $default),
                $messages
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($input, array $rules, ?string $group = null, array $messages = [], $default = null): self
    {
        if ($input instanceof Request) {
            return $this->request($input, $rules, $group, $messages, $default);
        }

        if (is_array($input)) {
            return $this->array($input, $rules, $group, $messages, $default);
        }

        if (is_object($input)) {
            return $this->object($input, $rules, $group, $messages, $default);
        }

        return $this->value($input, $rules, null, $group, $messages);
    }

    /**
     * Validates a single value with the given rules.
     *
     * @param mixed             $value
     * @param Validatable|array $rules
     * @param string            $key
     * @param string|null       $group
     * @param string[]          $messages
     *
     * @return self
     */
    public function value($value, $rules, string $key, ?string $group = null, array $messages = []): self
    {
        return $this->validateInput($value, new Configuration($rules, $key, $group), $messages);
    }

    /**
     * Gets the error count.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * Adds a validation error.
     *
     * @param string      $key
     * @param string      $message
     * @param string|null $group
     *
     * @return self
     */
    public function addError(string $key, string $message, ?string $group = null): self
    {
        if (!empty($group)) {
            $this->errors[$group][$key][] = $message;
        } else {
            $this->errors[$key][] = $message;
        }

        return $this;
    }

    /**
     * Gets one default messages.
     *
     * @param string $key
     *
     * @return string
     */
    public function getDefaultMessage($key): string
    {
        return $this->defaultMessages[$key] ?? '';
    }

    /**
     * Gets all default messages.
     *
     * @return string[]
     */
    public function getDefaultMessages(): array
    {
        return $this->defaultMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(string $key, $index = null, $group = null): string
    {
        if (null === $index) {
            return $this->getFirstError($key, $group);
        }

        if (!empty($group)) {
            return (string)($this->errors[$group][$key][$index] ?? '');
        }

        return (string)($this->errors[$key][$index] ?? '');
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(?string $key = null, ?string $group = null): array
    {
        if (!empty($key)) {
            if (!empty($group)) {
                return $this->errors[$group][$key] ?? [];
            }

            return $this->errors[$key] ?? [];
        }

        return $this->errors;
    }

    /**
     * Gets the first error of a parameter.
     *
     * @param string      $key
     * @param string|null $group
     *
     * @return string
     */
    public function getFirstError(string $key, ?string $group = null): string
    {
        if ($group && isset($this->errors[$group][$key])) {
            $first = array_slice($this->errors[$group][$key], 0, 1);

            return (string)array_shift($first);
        }

        if (isset($this->errors[$key])) {
            $first = array_slice($this->errors[$key], 0, 1);

            return (string)array_shift($first);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(string $key, ?string $group = null)
    {
        if (!empty($group)) {
            return $this->values[$group][$key] ?? null;
        }

        return $this->values[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(?string $group = null): array
    {
        if (!empty($group)) {
            return $this->values[$group] ?? [];
        }

        return $this->values;
    }

    /**
     * Gets the errors storage mode.
     *
     * @return bool
     */
    public function getShowValidationRules(): bool
    {
        return $this->showValidationRules;
    }

    /**
     * Removes validation errors.
     *
     * @param string|null $key
     * @param string|null $group
     *
     * @return self
     */
    public function removeErrors(?string $key = null, ?string $group = null): self
    {
        if (!empty($group)) {
            if ($key) {
                unset($this->errors[$group][$key]);
            } else {
                unset($this->errors[$group]);
            }
        } elseif ($key) {
            unset($this->errors[$key]);
        }

        return $this;
    }

    /**
     * Sets the default error message for a validation rule.
     *
     * @param string $rule
     * @param string $message
     *
     * @return self
     */
    public function setDefaultMessage(string $rule, string $message): self
    {
        $this->defaultMessages[$rule] = $message;

        return $this;
    }

    /**
     * Sets default error messages.
     *
     * @param string[] $messages
     *
     * @return self
     */
    public function setDefaultMessages(array $messages): self
    {
        $this->defaultMessages = $messages;

        return $this;
    }

    /**
     * Sets validation errors.
     *
     * @param string[]    $errors
     * @param string|null $key
     * @param string|null $group
     *
     * @return self
     */
    public function setErrors(array $errors, ?string $key = null, ?string $group = null): self
    {
        if (!empty($group)) {
            if ($key) {
                $this->errors[$group][$key] = $errors;
            } else {
                $this->errors[$group] = $errors;
            }
        } elseif ($key) {
            $this->errors[$key] = $errors;
        } else {
            $this->errors = $errors;
        }

        return $this;
    }

    /**
     * Sets the errors storage mode.
     *
     * @param bool $showValidationRules
     *
     * @return self
     */
    public function setShowValidationRules(bool $showValidationRules): self
    {
        $this->showValidationRules = $showValidationRules;

        return $this;
    }

    /**
     * Sets the value of a parameter.
     *
     * @param string      $key
     * @param mixed       $value
     * @param string|null $group
     *
     * @return self
     */
    public function setValue(string $key, $value, ?string $group = null): self
    {
        if (!empty($group)) {
            $this->values[$group][$key] = $value;
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Sets values of validated data.
     *
     * @param array       $values
     * @param string|null $group
     *
     * @return self
     */
    public function setValues(array $values, ?string $group = null): self
    {
        foreach ($values as $key => $value) {
            $this->setValue($key, $value, $group);
        }

        return $this;
    }

    /**
     * Gets the value of a property of an object.
     *
     * @param object     $object
     * @param string     $propertyName
     * @param mixed|null $default
     *
     * @return mixed
     */
    protected function getPropertyValue($object, string $propertyName, $default = null)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('The first argument should be an object');
        }

        if (!property_exists($object, $propertyName)) {
            return $default;
        }

        try {
            $reflectionProperty = new ReflectionProperty($object, $propertyName);
            $reflectionProperty->setAccessible(true);

            return $reflectionProperty->getValue($object);
        } catch (ReflectionException $e) {
            return $default;
        }
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
     * @param Validatable $validatable
     *
     * @return string[]
     */
    protected function getRulesNames(Validatable $validatable): array
    {
        if ($validatable instanceof AbstractComposite) {
            $rulesNames = [];
            foreach ($validatable->getRules() as $rule) {
                array_push($rulesNames, ...$this->getRulesNames($rule instanceof AbstractWrapper ? $rule->getValidatable() : $rule));
            }

            return $rulesNames;
        }

        return [lcfirst((new ReflectionClass($validatable))->getShortName())];
    }

    /**
     * Handles a validation exception.
     *
     * @param NestedValidationException $e
     * @param Configuration             $config
     * @param string[]                  $messages
     */
    protected function handleValidationException(NestedValidationException $e, Configuration $config, array $messages = [])
    {
        if ($config->hasMessage()) {
            $this->setErrors([$config->getMessage()], $config->getKey(), $config->getGroup());
        } else {
            $this->storeErrors($e, $config, $messages);
        }
    }

    /**
     * Merges default messages, global messages and individual messages.
     *
     * @param array $errors
     *
     * @return string[]
     */
    protected function mergeMessages(array $errors): array
    {
        $errors = array_filter(array_merge(...$errors));

        return $this->showValidationRules ? $errors : array_values($errors);
    }

    /**
     * Sets error messages after validation.
     *
     * @param NestedValidationException $e
     * @param Configuration             $config
     * @param string[]                  $messages
     */
    protected function storeErrors(NestedValidationException $e, Configuration $config, array $messages = [])
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

        $this->setErrors($this->mergeMessages($errors), $config->getKey(), $config->getGroup());
    }

    /**
     * Executes the validation of a value and handles errors.
     *
     * @param mixed         $input
     * @param Configuration $config
     * @param string[]      $messages
     *
     * @return self
     */
    protected function validateInput($input, Configuration $config, array $messages = []): self
    {
        try {
            $config->getValidationRules()->assert($input);
        } catch (NestedValidationException $e) {
            $this->handleValidationException($e, $config, $messages);
        }

        return $this->setValue($config->getKey(), $input, $config->getGroup());
    }
}
