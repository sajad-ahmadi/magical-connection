<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide common array utility methods.
 *
 * This static utility class provides reusable helpers for checking
 * array keys and normalizing numeric values.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Arr
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
     * Determine whether an array contains a non-empty value for a key.
     *
     * A key is considered present only when it exists and its value
     * is neither null nor an empty string.
     *
     * @since 1.0.0
     *
     * @param array $array Array to inspect.
     * @param mixed $key          Key to check.
     *
     * @return bool True when the key exists and contains a non-empty value.
     */
    public static function has(array $array, $key): bool
    {
        return array_key_exists($key, $array) && $array[$key] !== null && $array[$key] !== '';
    }

    /**
     * Determine whether an array contains a given key.
     *
     * Unlike {@see has()}, this method considers a key present even
     * when its value is null or an empty string.
     *
     * @since 1.0.0
     *
     * @param array $array Array to inspect.
     * @param mixed $key          Key to check.
     *
     * @return bool True when the key exists in the array.
     */
    public static function exists(array $array, $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * Normalize a list of numeric values.
     *
     * Converts values to absolute integers, removes zero and duplicate
     * values, and reindexes the resulting array.
     *
     * @since 1.0.0
     *
     * @param array $numbers Values to normalize.
     *
     * @return array Unique positive integer values.
     */
    public static function normalizeNumbers(array $numbers): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        'absint',
                        $numbers
                    )
                )
            )
        );
    }

}