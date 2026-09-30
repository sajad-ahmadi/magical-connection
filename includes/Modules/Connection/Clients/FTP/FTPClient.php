<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\FTP;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;

/**
 * Provide a high-level client for FTP connection operations.
 *
 * Coordinates the FTP connection, file, and directory services
 * through a single client interface.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class FTPClient
{
    /**
     * Manage the underlying FTP connection.
     *
     * @since 1.0.0
     */
    private FTPConnection $connection;

    /**
     * Manage FTP file operations.
     *
     * @since 1.0.0
     */
    private FTPFile $file;

    /**
     * Manage FTP directory operations.
     *
     * @since 1.0.0
     */
    private FTPDirectory $directory;

    /**
     * Create a new FTP client.
     *
     * @since 1.0.0
     *
     * @param FTPConnection $connection FTP connection handler.
     * @param FTPFile       $file       FTP file handler.
     * @param FTPDirectory  $directory  FTP directory handler.
     */
    public function __construct(FTPConnection $connection, FTPFile $file, FTPDirectory $directory)
    {
        $this->connection = $connection;
        $this->file = $file;
        $this->directory = $directory;
    }

    /**
     * Establish the FTP connection.
     *
     * @since 1.0.0
     *
     * @return bool True when the connection is established successfully.
     */
    public function connect(): bool
    {
        return $this->connection->connect();
    }

    /**
     * Close the FTP connection.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connection->disconnect();
    }

    /**
     * Determine whether the FTP connection is currently active.
     *
     * @since 1.0.0
     *
     * @return bool True when the client is connected.
     */
    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    /**
     * Get the FTP directory handler.
     *
     * @since 1.0.0
     *
     * @return FTPDirectory FTP directory handler.
     */
    public function directory(): FTPDirectory
    {
        return $this->directory;
    }

    /**
     * Get the FTP file handler.
     *
     * @since 1.0.0
     *
     * @return FTPFile FTP file handler.
     */
    public function file(): FTPFile
    {
        return $this->file;
    }

    /**
     * Get the connection configuration.
     *
     * @since 1.0.0
     *
     * @return ConnectionDTO Connection configuration.
     */
    public function config(): ConnectionDTO
    {
        return $this->connection->getConfig();
    }

}