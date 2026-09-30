<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide common URL utility methods.
 *
 * Provides helpers for normalizing URLs, validating URL syntax,
 * joining URL segments, and determining whether a URL contains
 * a scheme.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class Url
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
     * Normalize a URL string.
     *
     * Removes surrounding whitespace, collapses repeated slashes,
     * and restores the standard double slash after supported URL
     * schemes.
     *
     * @since 1.0.0
     *
     * @param string $url URL to normalize.
     *
     * @return string Normalized URL.
     */
    public static function normalize(string $url): string
    {
        $url = trim($url);
        $url = preg_replace('#/+#', '/', $url);
        return preg_replace(
            '#^(https?|ftp|ftps|ws|wss):/#',
            '$1://',
            $url
        );
    }

    /**
     * Determine whether a string is a valid URL.
     *
     * Validation is performed using PHP's URL validation filter.
     *
     * @since 1.0.0
     *
     * @param string $url URL to validate.
     *
     * @return bool True when the value is a valid URL.
     */
    public static function isValid(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Join multiple URL segments into a single URL.
     *
     * Empty segments are ignored and unnecessary slashes around
     * individual segments are removed before the resulting URL
     * is normalized.
     *
     * @since 1.0.0
     *
     * @param string ...$segments URL segments to join.
     *
     * @return string Joined and normalized URL.
     */
    public static function join(string ...$segments): string
    {
        $result = '';

        foreach ($segments as $index => $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }
            if ($index === 0) {
                $result = rtrim($segment, '/');
                continue;
            }
            $result .= '/' . trim($segment, '/');
        }

        return self::normalize($result);
    }

    /**
     * Determine whether a URL contains a scheme.
     *
     * Uses PHP URL parsing to detect the scheme component, such as
     * `http`, `https`, `ftp`, or `ftps`.
     *
     * @since 1.0.0
     *
     * @param string $url URL to inspect.
     *
     * @return bool True when the URL contains a scheme.
     */
    public static function hasScheme(string $url): bool
    {
        return parse_url($url, PHP_URL_SCHEME) !== null;
    }
}