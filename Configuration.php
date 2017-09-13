<?php

namespace Awurth\SlimValidation;

use InvalidArgumentException;
use Respect\Validation\Rules\AllOf;

class Configuration
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string[]
     */
    protected $messages;

    /**
     * @var AllOf
     */
    protected $rules;

    /**
     * Constructor.
     *
     * @param AllOf|array $options
     */
    public function __construct($options)
    {
        if ($options instanceof AllOf) {
            $this->rules = $options;
        } elseif (is_array($options)) {
            if (!isset($options['rules']) || !$options['rules'] instanceof AllOf) {
                throw new InvalidArgumentException('Validation rules are missing or invalid');
            }

            $this->message = $options['message'] ?? null;
            $this->messages = $options['messages'] ?? [];
            $this->rules = $options['rules'];
        } else {
            throw new InvalidArgumentException(sprintf('Options must be of type %s or array, %s given', AllOf::class, gettype($options)));
        }
    }

    /**
     * Gets the error message.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Gets individual rules messages.
     *
     * @return string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Gets the validation rules.
     *
     * @return AllOf
     */
    public function getValidationRules()
    {
        return $this->rules;
    }

    /**
     * Tells whether a single message has been set.
     *
     * @return bool
     */
    public function hasMessage()
    {
        return !empty($this->message);
    }

    /**
     * Tells whether individual rules messages have been set.
     *
     * @return bool
     */
    public function hasMessages()
    {
        return !empty($this->messages);
    }

    /**
     * Sets the error message.
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Sets individual rules messages.
     *
     * @param string[] $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Sets the validation rules.
     *
     * @param AllOf $rules
     */
    public function setValidationRules(AllOf $rules)
    {
        $this->rules = $rules;
    }
}
