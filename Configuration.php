<?php

namespace Awurth\SlimValidation;

use InvalidArgumentException;
use Respect\Validation\Rules\AllOf;

class Configuration
{
    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $key;

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
     * @param string $key
     * @param string $group
     */
    public function __construct($options, $key = null, $group = null)
    {
        if ($options instanceof AllOf) {
            if (empty($key)) {
                throw new InvalidArgumentException('The key must be set');
            }

            $this->rules = $options;
            $this->key = $key;
            $this->group = $group;
        } elseif (is_array($options)) {
            if (!isset($options['rules']) || !$options['rules'] instanceof AllOf) {
                throw new InvalidArgumentException('Validation rules are missing or invalid');
            }

            $this->key = $options['key'] ?? $key;

            if (!$this->hasKey()) {
                throw new InvalidArgumentException('The key must be set');
            }

            $this->group = $options['group'] ?? $group;
            $this->message = $options['message'] ?? null;
            $this->messages = $options['messages'] ?? [];
            $this->rules = $options['rules'];
        } else {
            throw new InvalidArgumentException(sprintf('Options must be of type %s or array, %s given', AllOf::class, gettype($options)));
        }
    }

    /**
     * Gets the group to use for errors and values storage.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Gets the key to use for errors and values storage.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
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
     * Tells whether a group has been set.
     *
     * @return bool
     */
    public function hasGroup()
    {
        return !empty($this->group);
    }

    /**
     * Tells whether a key has been set.
     *
     * @return bool
     */
    public function hasKey()
    {
        return !empty($this->key);
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
     * Sets the group to use for errors and values storage.
     *
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Sets the key to use for errors and values storage.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
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
