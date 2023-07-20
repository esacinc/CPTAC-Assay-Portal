<?php
namespace core\models\Utils;

final class DateTimeZoneUtils
{
    private function __construct()
    {
    }

    /**
     * Type-aware wrapper for {@see DateTimeZone} instance creation. Uses the runtime default timezone if a null value is provided. Otherwise, attempts to
     * (reasonably) convert the given value to a timezone string.
     *
     * @param float|int|string|null $time_zone_value
     * @return \DateTimeZone
     * @throws \InvalidArgumentException
     */
    public static function create($time_zone_value = null): \DateTimeZone
    {
        $time_zone_value_type = gettype($time_zone_value);

        try {
            switch ($time_zone_value_type) {
                case "NULL":
                    return new \DateTimeZone(date_default_timezone_get());

                case "double":
                    $time_zone_value = ((int)$time_zone_value);

                case "integer":
                    return new \DateTimeZone(sprintf("%+04d", $time_zone_value));

                case "string":
                    return new \DateTimeZone($time_zone_value);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid time zone value (type={$time_zone_value_type}): {$time_zone_value}", 0, $e);
        }

        throw new \InvalidArgumentException("Invalid time zone value type: {$time_zone_value_type}");
    }
}