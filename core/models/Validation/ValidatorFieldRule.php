<?php
namespace core\models\Validation;

class ValidatorFieldRule extends ValidatorRule
{
    private $params;

    public function __construct(string $name, string $msg, $callback, array $params)
    {
        parent::__construct($name, $msg, $callback);

        $this->params = $params;
    }

    public function hasParameters(): bool
    {
        return !empty($this->params);
    }

    public function &getParameters(): array
    {
        return $this->params;
    }

    public function setParameters(array $params): self
    {
        $this->params = $params;

        return $this;
    }
}