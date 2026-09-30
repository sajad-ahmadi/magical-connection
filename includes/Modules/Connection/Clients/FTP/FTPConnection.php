<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\FTP;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage the underlying FTP connection lifecycle.
 *
 * Handles establishing, authenticating, configuring, and closing
 * an FTP connection based on the provided connection configuration.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class FTPConnection
{
    /**
     * FTP connection configuration.
     *
     * @since 1.0.0
     */
    private ConnectionDTO $connection;

    /**
     * Active FTP connection resource.
     *
     * @since 1.0.0
     */
    private $resource = null;

    /**
     * Whether the FTP connection is currently established.
     *
     * @since 1.0.0
     */
    private bool $connected = false;

    /**
     * Create a new FTP connection handler.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection FTP connection configuration.
     */
    public function __construct(ConnectionDTO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Establish and authenticate the FTP connection.
     *
     * Reuses the existing connection when it is already active.
     * The connection is configured according to the provided timeout
     * and passive mode settings.
     *
     * @since 1.0.0
     *
     * @return bool True when the connection is established successfully.
     */
    public function connect(): bool
    {
        if ($this->connected) {
            return true;
        }

        $host = $this->connection->getHost();
        $port = $this->connection->getPort();
        $timeout = $this->connection->getTimeout();

        $resource = ftp_connect($host, $port, $timeout);

        if ($resource === false) {
            throw new MagicalConnectionException(
                'Unable to connect to FTP server',
                'ftp.connection_failed'
            );
        }

        $username = $this->connection->getUsername();
        $password = $this->connection->getPassword();


        if (!@ftp_login($resource, $username, $password)) {
            ftp_close($resource);
            throw new MagicalConnectionException(
                'FTP authentication failed.',
                'ftp.authentication_failed'
            );
        }

        if ($this->connection->getPassiveMod()) {
            if (!ftp_pasv($resource, true)) {
                ftp_close($resource);
                throw new MagicalConnectionException(
                    'Unable to enable FTP passive mode.',
                    'ftp.passive_mode_failed'
                );
            }
        }

        if (!ftp_set_option($resource, FTP_TIMEOUT_SEC, (int)$timeout)) {
            ftp_close($resource);
            throw new MagicalConnectionException(
                'Unable to set FTP timeout.',
                'ftp.option_failed'
            );
        }

        $this->resource = $resource;
        $this->connected = true;

        return true;
    }

    /**
     * Close the active FTP connection.
     *
     * Safely resets the connection state even when no active
     * FTP connection exists.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->resource !== null) {
            @ftp_close($this->resource);
        }

        $this->resource = null;
        $this->connected = false;
    }

    /**
     * Determine whether an active FTP connection exists.
     *
     * @since 1.0.0
     *
     * @return bool True when the connection is active.
     */
    public function isConnected(): bool
    {
        return $this->connected && $this->resource !== null;
    }

    /**
     * Get the active FTP connection resource.
     *
     * @since 1.0.0
     *
     * @return mixed Active FTP connection resource.
     */
    public function getConnection()
    {
        if (!$this->connected || !$this->resource) {
            throw new MagicalConnectionException(
                'FTP client is not connected.',
                'ftp.not_connected'
            );
        }

        return $this->resource;
    }

    /**
     * Get the FTP connection configuration.
     *
     * @since 1.0.0
     *
     * @return ConnectionDTO FTP connection configuration.
     */
    public function getConfig(): ConnectionDTO
    {
        return $this->connection;
    }

    /**
     * Open a stream to a remote FTP path.
     *
     * Builds an FTP stream URL using the configured credentials
     * and opens it with the requested stream mode.
     *
     * @since 1.0.0
     *
     * @param string $remotePath Remote FTP path.
     * @param string $mode       Stream opening mode.
     *
     * @return resource Active FTP stream.
     */
    public function openStream(string $remotePath, string $mode)
    {
        $username = rawurlencode($this->connection->getUsername());
        $password = rawurlencode($this->connection->getPassword());

        $url = sprintf(
            'ftp://%s:%s@%s%s',
            $username,
            $password,
            $this->connection->getHost(),
            $remotePath
        );

        $stream = @fopen($url, $mode);

        if ($stream === false) {
            throw new MagicalConnectionException(
                'Unable to open FTP stream.',
                'open_stream_failed'
            );
        }

        return $stream;
    }
}