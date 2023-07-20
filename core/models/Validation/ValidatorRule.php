<?php
namespace core\models\Validation;

class ValidatorRule
{
    protected $name;
    protected $msg;
    protected $callback;

    public function __construct(string $name, string $msg, $callback)
    {
        $this->name = $name;
        $this->msg = $msg;
        $this->callback = $callback;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback($callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->msg;
    }

    public function setMessage(string $msg): self
    {
        $this->msg = $msg;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}