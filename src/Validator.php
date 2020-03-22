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
            $validatable = ValidatableFactory::create($key, $options, $default);
            $this->validateInput($array[$key] ?? $validatable->getDefault(), $validatable, $messages);
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
            $validatable = ValidatableFactory::create($property, $options, $default);

            $input = $this->getPropertyAccessor()->isReadable($object, $property)
                ? $this->getPropertyAccessor()->getValue($object, $property)
                : $validatable->getDefault();

            $this->validateInput($input, $validatable, $messages);
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
            $validatable = ValidatableFactory::create($param, $options, $default);
            $this->validateInput(
                $this->getRequestParam($request, $param, $validatable->getDefault()),
                $validatable,
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
     * @param string      $path
     * @param string[]    $messages
     *
     * @return self
     */
    public function validate($input, $rules, string $path, array $messages = []): self
    {
        return $this->validateInput($input, ValidatableFactory::create($path,$rules), $messages);
    }

    public function getDefaultMessage(string $rule): string
    {
        return $this->defaultMessages[$rule] ?? '';
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

    protected function handleValidationException(NestedValidationException $e, Validatable $validatable, array $messages, $input): void
    {
        if ($message = $validatable->getMessage()) {
            $this->errors->add(new ValidationError($validatable->getPath(), $message, $input));
        } else {
            foreach ($this->findErrorMessages($e, $validatable, $messages) as $ruleName => $message) {
                $this->errors->add(
                    (new ValidationError($validatable->getPath(), $message, $input))->setRule($ruleName)
                );
            }
        }
    }

    protected function findErrorMessages(NestedValidationException $e, Validatable $validatable, array $messages = []): array
    {
        $errors = [
            $e->findMessages($this->getRulesNames($validatable->getValidationRules()))
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
        if ($validatableMessages = $validatable->getMessages()) {
            $errors[] = $e->findMessages($validatableMessages);
        }

        return array_filter(array_merge(...$errors));
    }

    protected function validateInput($input, Validatable $validatable, array $messages = []): self
    {
        try {
            $validatable->getValidationRules()->assert($input);
        } catch (NestedValidationException $e) {
            $this->handleValidationException($e, $validatable, $messages, $input);
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
