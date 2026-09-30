<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\HTTP;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;

/**
 * Provide a high-level client for HTTP connection operations.
 *
 * Coordinates the HTTP connection and file services through
 * a single client interface.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class HTTPClient
{

    /**
     * Manage the underlying HTTP connection.
     *
     * @since 1.0.0
     */
    private HTTPConnection $connection;

    /**
     * Manage HTTP file operations.
     *
     * @since 1.0.0
     */
    private HTTPFile $file;

    /**
     * Create a new HTTP client.
     *
     * @since 1.0.0
     *
     * @param HTTPConnection $connection HTTP connection handler.
     * @param HTTPFile       $file       HTTP file handler.
     */
    public function __construct(HTTPConnection $connection, HTTPFile $file)
    {
        $this->connection = $connection;
        $this->file = $file;
    }

    /**
     * Initialize the HTTP connection.
     *
     * @since 1.0.0
     *
     * @return bool True when the HTTP connection is available.
     */
    public function connect(): bool
    {
        return $this->connection->connect();
    }

    /**
     * Close the HTTP connection.
     *
     * HTTP is stateless, so no persistent connection is explicitly
     * closed by the client.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function disconnect(): void
    {
        // HTTP is stateless; there is no persistent connection to close.
    }

    /**
     * Determine whether the HTTP connection is available.
     *
     * @since 1.0.0
     *
     * @return bool True when the HTTP connection is available.
     */
    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    /**
     * Get the HTTP file handler.
     *
     * @since 1.0.0
     *
     * @return HTTPFile HTTP file handler.
     */
    public function file(): HTTPFile
    {
        return $this->file;
    }

    /**
     * Get the HTTP connection configuration.
     *
     * @since 1.0.0
     *
     * @return ConnectionDTO HTTP connection configuration.
     */
    public function config(): ConnectionDTO
    {
        return $this->connection->getConfig();
    }

}