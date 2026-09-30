<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide common string utility methods.
 *
 * Provides helpers for generating secure random strings and checking
 * whether a string starts with, ends with, or contains a given value.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class Str
{

    /**
     * Prevent instantiation of the utility class.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct()
    {
        throw new LogicException('Static class');
    }

    /**
     * Generate a cryptographically secure random string.
     *
     * Uses random_int() to select characters from the supported
     * alphanumeric character set.
     *
     * @since 1.0.0
     *
     * @param int $length Length of the generated string.
     *
     * @return string Random alphanumeric string.
     */
    public static function random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($characters) - 1;
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $max)];
        }

        return $result;
    }

    /**
     * Determine whether a string starts with a given value.
     *
     * @since 1.0.0
     *
     * @param string $haystack String to inspect.
     * @param string $needle   Value to search for at the beginning.
     *
     * @return bool True when the string starts with the given value.
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Determine whether a string ends with a given value.
     *
     * @since 1.0.0
     *
     * @param string $haystack String to inspect.
     * @param string $needle   Value to search for at the end.
     *
     * @return bool True when the string ends with the given value.
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Determine whether a string contains a given value.
     *
     * @since 1.0.0
     *
     * @param string $haystack String to inspect.
     * @param string $needle   Value to search for.
     *
     * @return bool True when the string contains the given value.
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}