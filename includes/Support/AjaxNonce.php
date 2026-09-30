<?php
declare(strict_types=1);

namespace MagicalConnection\Support;
/**
 * Handle AJAX nonce creation and verification.
 *
 * Provides a centralized interface for generating and validating
 * WordPress nonces used by Magical Connection AJAX requests.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class AjaxNonce
{
    /**
     * WordPress action used for Magical Connection AJAX nonces.
     *
     * @since 1.0.0
     */
    private const ACTION = 'magical_connection_nonce';

    /**
     * Request parameter containing the nonce value.
     *
     * @since 1.0.0
     */
    private const QUERY_ARG = 'nonce';

    /**
     * Create a nonce for Magical Connection AJAX requests.
     *
     * @since 1.0.0
     *
     * @return string Generated nonce.
     */
    public static function create(): string
    {
        return wp_create_nonce(self::ACTION);
    }

    /**
     * Verify the nonce supplied with an AJAX request.
     *
     * Returns the WordPress nonce verification result without
     * terminating the current request.
     *
     * @since 1.0.0
     *
     * @return false|int Nonce verification result.
     */
    public static function verify()
    {
        return check_ajax_referer(
            self::ACTION,
            self::QUERY_ARG,
            false
        );
    }

    /**
     * Verify the AJAX nonce and terminate the request on failure.
     *
     * Sends a JSON error response with HTTP status 403 when
     * the supplied nonce is invalid.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function verifyOrFail(): void
    {
        if (self::verify() !== false) {
            return;
        }

        wp_send_json_error([
            'message' => __(
                'Invalid security token.',
                MAGICAL_CONNECTION_TEXT_DOMAIN
            ),
        ], 403);
    }
}