<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide JSON encoding, decoding, and validation utilities.
 *
 * Centralizes common JSON operations and uses JSON_THROW_ON_ERROR
 * for encoding and decoding failures.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Json
{

    /**
     * Maximum nesting depth used when decoding JSON.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        throw new LogicException('Static class');
    }

    /**
     * Prevent instantiation of the utility class.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private const DEFAULT_DEPTH = 512;

    /**
     * Determine whether a string contains valid JSON.
     *
     * An empty string is considered invalid. Validation does not
     * throw an exception and only checks the JSON parser result.
     *
     * @since 1.0.0
     *
     * @param string $json JSON string to validate.
     *
     * @return bool True when the string contains valid JSON.
     */
    public static function isValid(string $json): bool
    {
        if ($json === '') {
            return false;
        }
        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Encode a value as a JSON string.
     *
     * JSON_THROW_ON_ERROR is always enabled so encoding failures
     * are reported as exceptions instead of returning false.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to encode.
     * @param int $flags   Additional JSON encoding flags.
     *
     * @return string Encoded JSON string.
     */
    public static function encode($value, int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES): string
    {
        return json_encode(
            $value,
            $flags | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Decode a JSON string into a PHP value.
     *
     * Uses a fixed maximum nesting depth and JSON_THROW_ON_ERROR
     * so malformed JSON is not silently converted into a null result.
     *
     * @since 1.0.0
     *
     * @param string $json      JSON string to decode.
     * @param bool $associative Whether JSON objects should be returned as arrays.
     *
     * @return mixed Decoded PHP value.
     */
    public static function decode(string $json, bool $associative = true)
    {
        return json_decode(
            $json,
            $associative,
            self::DEFAULT_DEPTH,
            JSON_THROW_ON_ERROR
        );
    }
}