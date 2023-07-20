<?php
namespace core\models\Validation;

class ValidatorResult
{
    private $errors = [];

    public function addError(array $fieldNames, string $msg): self
    {
        $this->errors[] = new ValidatorError($fieldNames, $msg);

        return $this;
    }

    public function hasErrors(string $fieldName = null): bool
    {
        if (empty($this->errors)) {
            return false;
        } else if ($fieldName === null) {
            return true;
        } else {
            foreach ($this->errors as &$error) {
                if ($error->hasFieldName($fieldName)) {
                    return true;
                }
            }

            return false;
        }
    }

    public function &getErrors(string $fieldName = null): array
    {
        if ($fieldName !== null) {
            $fieldErrors = [];

            if (!empty($this->errors)) {
                foreach ($this->errors as &$error) {
                    if ($error->hasFieldName($fieldName)) {
                        $fieldErrors[] = &$error;
                    }
                }
            }

            return $fieldErrors;
        } else {
            return $this->errors;
        }
    }
}