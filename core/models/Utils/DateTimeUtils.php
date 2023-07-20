<?php
namespace core\models\Utils;

final class DateTimeUtils
{
    private function __construct()
    {
    }

    /**
     * Wraps the creation of {@see DateTime} instances from Unix timestamps.
     *
     * @param float|int|null $timestamp
     * @param \DateTimeZone|float|int|string|null $time_zone
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    public static function createFromTimestamp($timestamp = null, $time_zone = null): \DateTime
    {
        return self::createFromFormat(self::createTimestamp($timestamp), $time_zone);
    }

    /**
     * Wraps the creation of {@see DateTime} instances for the sake of sanity. Although using {@see DateTime::createFromFormat} throws an exception if given a
     * format and/or time value that it cannot understand, a well-formed, but nonsensical time value (ex. February 32nd) will be
     * {@link http://php.net/manual/en/datetime.getlasterrors.php#102686 silently fumbled into a valid time in an undocumented manner}!
     *
     * @param string $format
     * @param string $time
     * @param \DateTimeZone|float|int|string|null $time_zone
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    public static function createFromFormat(string $format, string $time, $time_zone = null): \DateTime
    {
        $date_time = date_create_from_format($format, $time, (($time_zone instanceof \DateTimeZone) ? $time_zone : DateTimeZoneUtils::create($time_zone)));
        $msgs = date_get_last_errors();
        $errors_available = ($msgs["error_count"] !== 0);
        $warnings_available = ($msgs["warning_count"] !== 0);

        if (!$errors_available && !$warnings_available) {
            return $date_time;
        }

        $errors_str = "";

        if ($errors_available) {
            $errors = $msgs["errors"];
            $errors_str = StringUtils::delimitAssociated($errors, "; ");
        }

        $warnings_str = "";

        if ($warnings_available) {
            $warnings = $msgs["warnings"];
            $warnings_str = StringUtils::delimitAssociated($warnings, "; ");
        }

        throw new \InvalidArgumentException("Unable to create DateTime from format: format={$format}, time={$time}, time_zone={$time_zone}, errors=[{$errors_str}], warnings=[{$warnings_str}]");
    }

    /**
     * Wraps the creation of Unix timestamp strings. Using {@see microtime} with a false argument (to get a string representation) results in a uselessly
     * formatted value ("msec sec") of variable length. Using {@see microtime} with true to get a float requires conversion back to a string, which is
     * dependent on the php.ini precision setting. Using the @<timestamp> {@see DateTime} format does not allow for precision beyond a second.
     * {@link http://php.net/manual/en/datetime.createfromformat.php#119362 Using a "U.u" format (hilariously) breaks if/when the microsecond fraction is 0}.
     *
     * @param float|int|null $timestamp
     * @return string
     */
    public static function createTimestamp($timestamp = null): string
    {
        switch (gettype($timestamp)) {
            case "NULL":
                $timestamp = microtime(true);
                break;

            case "double":
                break;

            default:
                $timestamp = ((float)$timestamp);
                break;
        }

        return sprintf("%.6f", $timestamp);
    }
}