<?php
namespace core\models\Validation;

class ValidatorError
{
    private $fieldNames;
    private $msg;

    public function __construct(array $fieldNames, string $msg)
    {
        $this->fieldNames = $fieldNames;
        $this->msg = $msg;

        sort($this->fieldNames);
    }

    public function hasFieldName($fieldName): bool
    {
        return in_array($fieldName, $this->fieldNames, true);
    }

    public function &getFieldNames(): array
    {
        return $this->fieldNames;
    }

    public function getMessage(): string
    {
        return $this->msg;
    }
}