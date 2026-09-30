<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\HTTP;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Support\Json;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage HTTP API communication for a connection.
 *
 * Handles authenticated HTTP requests, API response parsing,
 * connection state, and remote API availability checks.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class HTTPConnection
{
    /**
     * HTTP connection configuration.
     *
     * @since 1.0.0
     */
    private ConnectionDTO $connection;

    /**
     * Whether the remote HTTP API has been successfully initialized.
     *
     * @since 1.0.0
     */
    private bool $connected = false;

    /**
     * Create a new HTTP connection handler.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection HTTP connection configuration.
     */
    public function __construct(ConnectionDTO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Check the availability of the remote HTTP API.
     *
     * Sends a ping request and marks the connection as active when
     * the remote API responds successfully.
     *
     * @since 1.0.0
     *
     * @return bool True when the remote API is available.
     */
    public function connect(): bool
    {
        $this->request(
            'GET',
            'ping'
        );
        $this->connected = true;
        return true;
    }

    /**
     * Determine whether the HTTP API is currently available.
     *
     * @since 1.0.0
     *
     * @return bool True when the connection has been initialized successfully.
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Reset the HTTP connection state.
     *
     * HTTP does not maintain a persistent connection at this layer,
     * so disconnecting only resets the client state.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connected = false;
    }

    /**
     * Send an authenticated request to the remote HTTP API.
     *
     * Builds the API endpoint from the configured domain and action,
     * sends the request through cURL, and parses the JSON response.
     *
     * @since 1.0.0
     *
     * @param string               $method HTTP request method.
     * @param string               $action API action name.
     * @param array  $data   Request data.
     * @param array  $files Files or upload values.
     *
     * @return array Parsed API response.
     */
    public function request(string $method, string $action, array $data = [], array $files = []): array
    {
        $url = $this->connection->getDomain() . '/http-api/?action=' . rawurlencode($action);

        $curl = curl_init($url);

        if ($curl === false) {
            throw new MagicalConnectionException(
                'Unable to initialize cURL.',
                'http.curl_init_failed'
            );
        }

        $headers = [
            'Authorization: Bearer ' . $this->connection->getToken(),
            'Accept: application/json',
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 300,
        ];

        if (!empty($data) || !empty($files)) {
            $postFields = $data;
            foreach ($files as $name => $file) {
                $postFields[$name] = $file;
            }
            $options[CURLOPT_POSTFIELDS] = $postFields;
        }

        curl_setopt_array(
            $curl,
            $options
        );

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new MagicalConnectionException(
                'Unable to connect to HTTP server',
                'http.connection_failed'
            );
        }

        $statusCode = (int)curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        //curl_close($curl);

        return $this->parseResponse(
            $response,
            $statusCode
        );
    }

    /**
     * Parse and validate an HTTP API response.
     *
     * Rejects non-successful HTTP status codes, invalid JSON responses,
     * and API responses that explicitly report an application-level error.
     *
     * @since 1.0.0
     *
     * @param string $response   Raw HTTP response body.
     * @param int    $statusCode HTTP response status code.
     *
     * @return array Parsed API response.
     */
    public function parseResponse(string $response, int $statusCode): array
    {
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new MagicalConnectionException(
                sprintf(
                    'HTTP request failed with status code %d.',
                    $statusCode
                ),
                'http.error'
            );
        }
        if (!Json::isValid($response)) {
            throw new MagicalConnectionException(
                'Invalid JSON response from HTTP API.',
                'http.invalid_json'
            );
        }

        $data = Json::decode($response);

        if (isset($data['success']) && $data['success'] === false) {
            $message = isset($data['error']['message']) ? $data['error']['message'] : 'Remote API returned an error.';
            throw new MagicalConnectionException(
                sprintf('HTTP request failed with message: %s', $message),
                'http.connection_failed'
            );
        }

        return $data;
    }

    /**
     * Get the HTTP connection configuration.
     *
     * @since 1.0.0
     *
     * @return ConnectionDTO HTTP connection configuration.
     */
    public function getConfig(): ConnectionDTO
    {
        return $this->connection;
    }
}