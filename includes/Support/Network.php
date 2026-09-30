<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide common network utility methods.
 *
 * Provides helpers for validating IP addresses, resolving domains
 * and URLs to IP addresses, and checking whether a network endpoint
 * is reachable on a given port.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class Network
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
     * Determine whether a value is a valid IP address.
     *
     * Supports both IPv4 and IPv6 addresses.
     *
     * @since 1.0.0
     *
     * @param string $ip IP address to validate.
     *
     * @return bool True when the value is a valid IP address.
     */
    public static function isIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Resolve a domain name to an IP address.
     *
     * Resolves the domain using the system DNS resolver and returns
     * the resolved IP address when resolution succeeds.
     *
     * @since 1.0.0
     *
     * @param string $domain Domain name to resolve.
     *
     * @return string|null Resolved IP address or null when resolution fails.
     */
    public static function resolveDomain(string $domain): ?string
    {
        $ip = gethostbyname($domain);
        //$records = dns_get_record($domain, DNS_A + DNS_AAAA);

        return self::isIp($ip) ? $ip : null;
    }

    /**
     * Resolve the host of a URL to an IP address.
     *
     * Adds an HTTP scheme when the URL does not contain one before
     * extracting and resolving its host.
     *
     * @since 1.0.0
     *
     * @param string $url URL whose host should be resolved.
     *
     * @return string|null Resolved IP address or null when the URL
     *                     host cannot be resolved.
     */
    public static function resolveUrl(string $url): ?string
    {
        if (!Url::hasScheme($url)) {
            $url = 'http://' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return null;
        }

        return self::resolveDomain($host);
    }

    /**
     * Determine whether a network endpoint is reachable.
     *
     * Attempts to establish a TCP connection to the specified host
     * and port within the given timeout.
     *
     * @since 1.0.0
     *
     * @param string $host    Hostname or IP address to test.
     * @param int    $port    TCP port to test.
     * @param int    $timeout Connection timeout in seconds.
     *
     * @return bool True when a TCP connection can be established.
     */
    public static function isReachable(string $host, int $port = 80, int $timeout = 5): bool
    {

        $connection = @fsockopen(
            $host,
            $port,
            $errno,
            $errstr,
            $timeout
        );

        if (!$connection) {
            return false;
        }

        fclose($connection);

        return true;
    }
}