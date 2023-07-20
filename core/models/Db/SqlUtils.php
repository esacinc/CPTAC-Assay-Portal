<?php
namespace core\models\Db;

use core\models\Utils\StringUtils;

final class SqlUtils
{
    const FALSE_KEYWORD = "false";
    const NULL_KEYWORD = "null";
    const TRUE_KEYWORD = "true";

    private function __construct()
    {
    }

    public static function implodeFieldAssignments(array &$fields): string
    {
        return StringUtils::implodeWalk(function (string &$sql, $field_key, $field_value, $field_index): string
        {
            $field_value = self::toLiteral($field_value);

            return (($field_index > 0) ? "{$sql}, {$field_key} = {$field_value}" : "{$field_key} = {$field_value}");
        }, $fields);
    }

    public static function implodeFieldValues(array &$fields): string
    {
        return StringUtils::implodeWalk(function (string &$sql, $field_name, $field_value, $field_index): string
        {
            $field_value = self::toLiteral($field_value);

            return (($field_index > 0) ? "{$sql}, {$field_value}" : $field_value);
        }, $fields);
    }

    public static function implodeFieldNames(array &$fields): string
    {
        return StringUtils::delimitKeys($fields);
    }

    public static function extractFields(array &$data, array &$fields = []): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                if (self::isParameterName($key)) {
                    $fields[self::toFieldName($key)] = $key;
                } else {
                    $fields[$key] = self::toParameterName($key);
                }
            }
        }

        return $fields;
    }

    public static function toLiteral($value): string
    {
        switch (gettype($value)) {
            case "NULL":
                return self::NULL_KEYWORD;

            case "boolean":
                return ($value ? self::TRUE_KEYWORD : self::FALSE_KEYWORD);

            case "string":
                return $value;

            default:
                return ((string)$value);
        }
    }

    public static function toFieldName(string $name): string
    {
        return (self::isParameterName($name) ? substr($name, 1) : $name);
    }

    public static function toParameterName(string $name): string
    {
        $name = self::undelimitIdentifier($name);

        return (!self::isParameterName($name) ? ":{$name}" : $name);
    }

    public static function isParameterName(string $name): bool
    {
        return ($name[0] === ":");
    }

    public static function undelimitIdentifier(string $identifier): string
    {
        return (self::isDelimitedIdentifier($identifier) ? substr($identifier, 1, -1) : $identifier);
    }

    public static function delimitIdentifier(string $identifier): string
    {
        return (!self::isDelimitedIdentifier($identifier) ? "`{$identifier}`" : $identifier);
    }

    public static function isDelimitedIdentifier(string $str): bool
    {
        return (preg_match('/^`[^`]+`$/', $str) === 1);
    }
}