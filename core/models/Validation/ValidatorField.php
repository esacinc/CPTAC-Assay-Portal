<?php
namespace core\models\Validation;

class ValidatorField
{
    private $name;
    private $displayName;
    private $defaultValue = "";
    private $required = false;
    private $rules = [];

    public function __construct(string $name)
    {
        $this->displayName = ucfirst(str_replace("_", " ", ($this->name = $name)));
    }

    public function &getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addRule(string $name, string $msg, $callback, array $params): self
    {
        $this->rules[$name] = new ValidatorFieldRule($name, $msg, $callback, $params);

        return $this;
    }

    public function hasRules(): bool
    {
        return !empty($this->rules);
    }

    /**
     * @param string $name
     * @return ValidatorFieldRule|null
     */
    public function getRule(string $name)
    {
        return $this->rules[$name];
    }

    /**
     * @return ValidatorFieldRule[]
     */
    public function &getRules(): array
    {
        return $this->rules;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }
}