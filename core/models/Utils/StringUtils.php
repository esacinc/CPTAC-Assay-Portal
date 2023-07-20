<?php
namespace core\models\Utils;

final class StringUtils
{
    private function __construct()
    {
    }

    /**
     * @param callable $callback
     * @param \Traversable|array $data
     * @param string $str
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function &implodeWalk(callable $callback, &$data = null, string &$str = ""): string
    {
        if (($data === null) || (is_array($data) && empty($data))) {
            return $str;
        } else if (is_iterable($data)) {
            $index = -1;

            foreach ($data as $key => &$value) {
                $index++;
                $str = $callback($str, $key, $value, $index, $data);
            }

            return $str;
        } else {
            throw new \InvalidArgumentException("Data must be an iterable or null.");
        }
    }

    /**
     * @param \Traversable|array $data
     * @param string $entry_delim
     * @param string $entry_parts_delim
     * @param string $str
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function &delimitAssociated(&$data = null, string $entry_delim = ", ", string $entry_parts_delim = "=", string &$str = ""): string
    {
        if (($data === null) || (is_array($data) && empty($data))) {
            return $str;
        } else if (is_iterable($data)) {
            $first_entry = true;

            foreach ($data as $key => &$value) {
                if (!$first_entry) {
                    $str .= "{$entry_delim}{$key}{$entry_parts_delim}{$value}";
                } else {
                    $str = "{$key}{$entry_parts_delim}{$value}";
                    $first_entry = false;
                }
            }

            return $str;
        } else {
            throw new \InvalidArgumentException("Data must be an iterable or null.");
        }
    }

    /**
     * @param \Traversable|array $data
     * @param string $delim
     * @param string $str
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function &delimitKeys(&$data = null, string $delim = ", ", string &$str = ""): string
    {
        if (($data === null) || (is_array($data) && empty($data))) {
            return $str;
        } else if (is_iterable($data)) {
            $first_entry = true;

            foreach ($data as $key => &$value) {
                if (!$first_entry) {
                    $str .= "{$delim}{$key}";
                } else {
                    $str .= $key;
                    $first_entry = false;
                }
            }

            return $str;
        } else {
            throw new \InvalidArgumentException("Data must be an iterable or null.");
        }
    }
    
    /**
     * @param \Traversable|array $data
     * @param string $delim
     * @param string $str
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function &delimit(&$data = null, string $delim = ", ", string &$str = ""): string
    {
        if (($data === null) || (is_array($data) && empty($data))) {
            return $str;
        } else if (is_iterable($data)) {
            $first_entry = true;

            foreach ($data as $key => &$value) {
                if (!$first_entry) {
                    $str .= "{$delim}{$value}";
                } else {
                    $str .= $value;
                    $first_entry = false;
                }
            }

            return $str;
        } else {
            throw new \InvalidArgumentException("Data must be an iterable or null.");
        }
    }
}