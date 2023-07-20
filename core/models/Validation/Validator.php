<?php
namespace core\models\Validation;

class Validator
{
    private $rules = [];
    private $fields = [];

    public function __construct()
    {
        $this->addRule("alpha", "must contain only letters");
        $this->addRule("alphaNum", "must contain only letters and/or numbers", [self::class, "validateAlphaNumeric"]);
        $this->addRule("between", "must be between %d and %d");
        $this->addRule("boolean", "must be a boolean");
        $this->addRule("date", "must be a valid date");
        $this->addRule("dateAfter", "must be a valid date after %2s");
        $this->addRule("dateBefore", "must be a valid date before %2s");
        $this->addRule("dateBetween", "must be a valid date between %2s and %3s");
        $this->addRule("email", "must be a valid email address");
        $this->addRule("equals", "must be equal to %s");
        $this->addRule("float", "must be a decimal number");
        $this->addRule("hex", "must be hex-encoded");
        $this->addRule("in", "must be equal to one of %s");
        $this->addRule("integer", "must be an integer");
        $this->addRule("ip", "must be a valid IP address");
        $this->addRule("ipv4", "must be a valid IPv4 address");
        $this->addRule("ipv6", "must be a valid IPv6 address");
        $this->addRule("length", "must be exactly %d character(s) long");
        $this->addRule("lengthBetween", "must be between %d and %d character(s) long");
        $this->addRule("lengthMax", "must be no more than %d character(s) long", [self::class, "validateLengthMaximum"]);
        $this->addRule("lengthMin", "must be at least %d character(s) long", [self::class, "validateLengthMinimum"]);
        $this->addRule("max", "must be less than or equal to %d", [self::class, "validateMaximum"]);
        $this->addRule("min", "must be greater than or equal to %d", [self::class, "validateMinimum"]);
        $this->addRule("notEquals", "must not be equal to %s");
        $this->addRule("notIn", "must not be equal to one of %s");
        $this->addRule("numeric", "must be a numeric value");
        $this->addRule("phoneNum", "must be a valid phone number", [self::class, "validatePhoneNumber"]);
        $this->addRule("regex", "must contain a valid value");
        $this->addRule("required", "is required");
        $this->addRule("word", "must contain only letters, numbers, and/or underscores");
        $this->addRule("wordHyphen", "must contain only letters, numbers, underscores, and/or hyphens");
    }

    public function validate(array $data): ValidatorResult
    {
        $result = new ValidatorResult();

        if (!$this->hasFields()) {
            return $result;
        }

        foreach ($this->fields as $fieldName => $field) {
            if (!isset($data[$fieldName]) || ($data[$fieldName] === $field->getDefaultValue())) {
                if ($field->isRequired()) {
                    $result->addError([$fieldName], self::formatMessage($field->getDisplayName(), $this->getRule("required")->getMessage()));
                }

                continue;
            }

            foreach ($field->getRules() as $fieldRuleName => $fieldRule) {
                if (!call_user_func($fieldRule->getCallback(), $fieldName, $data[$fieldName], $fieldRule->getParameters())) {
                    $result->addError([$fieldName], self::formatMessage($field->getDisplayName(), $fieldRule->getMessage(), $fieldRule->getParameters()));
                }
            }
        }

        return $result;
    }

    private static function formatMessage(string $fieldDisplayName, string $msg, array $params = []): string
    {
        if (!empty($params)) {
            $msgArgs = [];

            foreach ($params as $param) {
                $msgArgs[] = (is_array($param) ? implode(", ", $param) : $param);
            }

            $msg = vsprintf($msg, $msgArgs);
        }

        return ($fieldDisplayName . " " . $msg);
    }

    private static function validateAlpha(string $fieldName, $value, array $params): bool
    {
        return preg_match('/^[[:alpha:]]+$/', $value);
    }

    private static function validateAlphaNumeric(string $fieldName, $value, array $params): bool
    {
        return preg_match('/^[[:alnum:]]+$/', $value);
    }

    private static function validateBetween(string $fieldName, $value, array $params): bool
    {
        return (($value >= $params[0]) && ($value <= $params[1]));
    }

    private static function validateBoolean(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE) !== null);
    }

    private static function validateDate(string $fieldName, $value, array $params): bool
    {
        return (self::parseDate($params[0], $value) instanceof \DateTime);
    }

    private static function validateDateAfter(string $fieldName, $value, array $params): bool
    {
        $date = self::parseDate($params[0], $value);

        return (($date instanceof \DateTime) && ($date->getTimestamp() > $params[1]->getTimestamp()));
    }

    private static function validateDateBefore(string $fieldName, $value, array $params): bool
    {
        $date = self::parseDate($params[0], $value);

        return (($date instanceof \DateTime) && ($date->getTimestamp() < $params[1]->getTimestamp()));
    }

    private static function validateDateBetween(string $fieldName, $value, array $params): bool
    {
        $date = self::parseDate($params[0], $value);

        if (!($date instanceof \DateTime)) {
            return false;
        }

        $timestamp = $date->getTimestamp();

        return (($timestamp > $params[1]->getTimestamp()) && ($timestamp < $params[2]->getTimestamp()));
    }

    private static function validateEmail(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_EMAIL) !== false);
    }

    private static function validateEquals(string $fieldName, $value, array $params): bool
    {
        return ($value == $params[0]);
    }

    private static function validateFloat(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_FLOAT) !== false);
    }

    private static function validateHex(string $fieldName, $value, array $params): bool
    {
        return preg_match('/^[a-fA-F0-9]+/', $value);
    }

    private static function validateIn(string $fieldName, $value, array $params): bool
    {
        return in_array($value, $params[0], true);
    }

    private function validateInteger(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_INT) !== false);
    }

    private static function validateIp(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_IP) !== false);
    }

    private static function validateIpv4(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) !== false);
    }

    private static function validateIpv6(string $fieldName, $value, array $params): bool
    {
        return (filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false);
    }
    
    private static function validateLength(string $fieldName, $value, array $params): bool
    {
        return (mb_strlen($value) === $params[0]);
    }

    private static function validateLengthBetween(string $fieldName, $value, array $params): bool
    {
        $valueLen = mb_strlen($value);

        return (($valueLen >= $params[0]) && ($valueLen <= $params[1]));
    }

    private static function validateLengthMaximum(string $fieldName, $value, array $params): bool
    {
        return (mb_strlen($value) <= $params[0]);
    }

    private static function validateLengthMinimum(string $fieldName, $value, array $params): bool
    {
        return (mb_strlen($value) >= $params[0]);
    }

    private static function validateMaximum(string $fieldName, $value, array $params): bool
    {
        return ($value <= $params[0]);
    }

    private static function validateMinimum(string $fieldName, $value, array $params): bool
    {
        return ($value >= $params[0]);
    }

    private static function validateNotEquals(string $fieldName, $value, array $params): bool
    {
        return ($value != $params[0]);
    }

    private static function validateNotIn(string $fieldName, $value, array $params): bool
    {
        return !in_array($value, $params[0], true);
    }

    private static function validatePhoneNumber(string $fieldName, $value, array $params): bool
    {
        return preg_match('/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/', $value);
    }

    private static function validateRegex(string $fieldName, $value, array $params): bool
    {
        return preg_match($params[0], $value);
    }

    private static function validateRequired(string $fieldName, $value, array $params): bool
    {
        return true;
    }

    private static function validateWord(string $fieldName, $value, array $params): bool
    {
        return preg_match('/^\w+$/', $value);
    }

    private static function validateWordHyphen(string $fieldName, $value, array $params): bool
    {
        return preg_match('/^[\w\-]+$/', $value);
    }

    private static function parseDate(string $format, string $value)
    {
        $date = date_create_from_format($format, $value);
        $dateErrors = date_get_last_errors();

        return ((($dateErrors["error_count"] > 0) || ($dateErrors["warning_count"] > 0)) ? $dateErrors : $date);
    }

    public function addField(string $name, array $rules, string $displayName = null, $defaultValue = null): self
    {
        $field = new ValidatorField($name);
        $rule = null;
        $ruleParams = null;

        foreach ($rules as $ruleValue) {
            if (is_string($ruleValue)) {
                if ($ruleValue === "required") {
                    $field->setRequired();

                    continue;
                }

                $rule = $this->rules[$ruleValue];
                $ruleParams = [];
            } else if (is_array($ruleValue)) {
                $rule = $this->rules[$ruleValue[0]];
                $ruleParams = array_slice($ruleValue, 1);
            }

            $field->addRule($rule->getName(), $rule->getMessage(), $rule->getCallback(), $ruleParams);
        }

        if ($displayName !== null) {
            $field->setDisplayName($displayName);
        }

        if ($defaultValue !== null) {
            $field->setDefaultValue($defaultValue);
        }

        $this->fields[$name] = $field;

        return $this;
    }

    public function hasFields(): bool
    {
        return !empty($this->fields);
    }

    /**
     * @param string $name
     * @return ValidatorField|null
     */
    public function getField(string $name)
    {
        return $this->fields[$name];
    }

    /**
     * @return ValidatorField[]
     */
    public function &getFields(): array
    {
        return $this->fields;
    }

    public function addRule(string $name, string $msg, $callback = null): self
    {
        $this->rules[$name] = new ValidatorRule($name, $msg, ($callback ?? [self::class, ("validate" . ucfirst($name))]));

        return $this;
    }

    public function hasRules(): bool
    {
        return !empty($this->rules);
    }

    /**
     * @param string $name
     * @return ValidatorRule|null
     */
    public function getRule(string $name)
    {
        return $this->rules[$name];
    }

    /**
     * @return ValidatorRule[]
     */
    public function &getRules(): array
    {
        return $this->rules;
    }
}