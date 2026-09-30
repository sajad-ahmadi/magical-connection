<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Services;

use MagicalConnection\Modules\Connection\Clients\FTP\FTPClient;
use MagicalConnection\Modules\Connection\Clients\FTP\FTPConnection;
use MagicalConnection\Modules\Connection\Clients\FTP\FTPDirectory;
use MagicalConnection\Modules\Connection\Clients\FTP\FTPFile;
use MagicalConnection\Modules\Connection\Clients\HTTP\HTTPClient;
use MagicalConnection\Modules\Connection\Clients\HTTP\HTTPConnection;
use MagicalConnection\Modules\Connection\Clients\HTTP\HTTPFile;
use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Create connection clients based on the configured protocol.
 *
 * Resolves a connection DTO to the appropriate client implementation
 * and assembles the services required by that client.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class ConnectionFactory
{
    /**
     * Supported connection protocols and their client identifiers.
     *
     * @since 1.0.0
     * @var array
     *
     */
    public static array $PROTOCOLS = [
        'ftp'   => 'FTP',
        'http'  => 'HTTP',
        'https' => 'HTTPS',
    ];

    /**
     * Create a client for the configured connection protocol.
     *
     * FTP connections receive dedicated connection, directory, and
     * file handlers. HTTP and HTTPS connections use the HTTP client
     * and file handler.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection configuration.
     *
     * @return FTPClient|HTTPClient Connection client for the configured protocol.
     */
    public function create(ConnectionDTO $connection)
    {
        switch (strtolower($connection->getProtocol())) {
            case 'ftp':
                $ftpConnection = new FTPConnection($connection);
                $ftpDirectory = new FTPDirectory($ftpConnection);
                $ftpFile = new FTPFile(
                    $ftpConnection,
                    $ftpDirectory
                );

                return new FTPClient(
                    $ftpConnection,
                    $ftpFile,
                    $ftpDirectory
                );

            case 'http':
            case 'https':
                $httpConnection = new HTTPConnection($connection);
                $httpFile = new HTTPFile($httpConnection);

                return new HTTPClient(
                    $httpConnection,
                    $httpFile
                );

            default:
                throw new MagicalConnectionException(
                    'Unsupported connection protocol: ' . $connection->getProtocol(),
                    'unsupported_connection_protocol'
                );
        }
    }
}