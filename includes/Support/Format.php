<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide common value formatting utilities.
 *
 * Provides helpers for converting byte values into human-readable
 * storage units and formatting durations into compact time values.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Format
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
     * Format a byte value using a human-readable unit.
     *
     * Converts the value to the most appropriate unit from bytes
     * through petabytes and rounds the result to the requested
     * precision.
     *
     * Negative values are treated as zero.
     *
     * @since 1.0.0
     *
     * @param mixed $bytes   Number of bytes to format.
     * @param int $precision Number of decimal places.
     *
     * @return string Formatted byte value with its unit.
     */
    public static function bytes($bytes, int $precision = 2): string
    {

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        $bytes /= pow(1024, $power);

        return round($bytes, $precision) . ' ' . $units[$power];
    }

    /**
     * Format a duration in seconds as a compact human-readable value.
     *
     * Includes only non-zero units and uses days, hours, minutes,
     * and seconds. A zero duration is represented as `0s`.
     *
     * @since 1.0.0
     *
     * @param int $seconds Duration in seconds.
     *
     * @return string Formatted duration.
     */
    public static function seconds(int $seconds): string
    {
        $days = floor($seconds / 86400);

        $seconds %= 86400;
        $hours = floor($seconds / 3600);
        $seconds %= 3600;
        $minutes = floor($seconds / 60);
        $seconds %= 60;

        $parts = [];

        if ($days) {
            $parts[] = "{$days}d";
        }
        if ($hours) {
            $parts[] = "{$hours}h";
        }
        if ($minutes) {
            $parts[] = "{$minutes}m";
        }
        if ($seconds || empty($parts)) {
            $parts[] = "{$seconds}s";
        }

        return implode(' ', $parts);
    }
}