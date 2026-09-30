<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Services;

use MagicalConnection\Modules\Connection\Clients\FTP\FTPClient;
use MagicalConnection\Modules\Connection\Clients\HTTP\HTTPClient;
use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Modules\Connection\Repositories\ConnectionCredentialRepository;
use MagicalConnection\Modules\Connection\Repositories\ConnectionRepository;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage connection retrieval and client creation.
 *
 * Coordinates connection and credential repositories to build complete
 * connection DTOs and delegates client creation to the connection factory.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class ConnectionsManager
{
    /**
     * Repository responsible for connection credentials.
     *
     * @since 1.0.0
     */
    private ConnectionCredentialRepository $connectionCredentialRepository;
    /**
     * Repository responsible for connection records.
     *
     * @since 1.0.0
     */
    private ConnectionRepository $connectionRepository;

    /**
     * Currently loaded connection DTO.
     *
     * Holds the connection used by the client creation methods.
     *
     * @since 1.0.0
     */
    private ConnectionDTO $connectionDTO;

    /**
     * Factory responsible for creating protocol-specific clients.
     *
     * @since 1.0.0
     */
    private ConnectionFactory $connectionFactory;

    /**
     * Create a new connections manager.
     *
     * @since 1.0.0
     *
     * @param ConnectionCredentialRepository $connectionsCredentialsRepository Connection credentials repository.
     * @param ConnectionRepository $connectionsRepository Connection repository.
     * @param ConnectionFactory $connectionFactory Connection client factory.
     */

    public function __construct(ConnectionCredentialRepository $connectionsCredentialsRepository, ConnectionRepository $connectionsRepository, ConnectionFactory $connectionFactory
    )
    {
        $this->connectionCredentialRepository = $connectionsCredentialsRepository;
        $this->connectionRepository = $connectionsRepository;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * Retrieve all stored connections.
     *
     * When requested, raw database records are returned. Otherwise,
     * the repository returns connection DTO instances.
     *
     * @since 1.0.0
     *
     * @param bool $is_array Whether to return raw database records.
     *
     * @return array Connection records.
     */
    public function getConnections(bool $is_array = false): ?array
    {
        return $this->connectionRepository->getConnections($is_array);
    }

    /**
     * Retrieve a complete connection by ID.
     *
     * Loads the connection configuration and its associated credentials
     * into a single ConnectionDTO instance.
     *
     * @since 1.0.0
     *
     * @param int $id Connection ID.
     *
     * @return ConnectionDTO Complete connection configuration and credentials.
     */
    public function get(int $id): ConnectionDTO
    {

        $connectionDTO = new ConnectionDTO();

        $connectionDTO->setId($id);
        $connectionDTO->setCredentialsId($id);

        if (!$this->connectionRepository->get($connectionDTO)) {
            throw new MagicalConnectionException(
                'connection not found.',
                'connection_not_found'
            );
        }

        if (!$this->connectionCredentialRepository->get($connectionDTO)) {
            throw new MagicalConnectionException(
                'Connection credentials not found.',
                'connection_credentials_not_found'
            );
        }

        $this->connectionDTO = $connectionDTO;

        return $connectionDTO;
    }

    /**
     * Create a client for the default connection.
     *
     * Loads the default connection together with its credentials
     * and creates the corresponding protocol-specific client.
     *
     * @since 1.0.0
     *
     * @return FTPClient|HTTPClient Connection client for the default connection.
     */
    public function client()
    {
        $this->getDefaultConnection();
        return $this->connectionFactory->create($this->connectionDTO);
    }

    /**
     * Create a client for a specific connection.
     *
     * Loads the requested connection together with its credentials
     * and creates the corresponding protocol-specific client.
     *
     * @since 1.0.0
     *
     * @param int $connectionId Connection ID.
     *
     * @return FTPClient|HTTPClient Connection client for the requested connection.
     */
    public function clientById(int $connectionId)
    {
        $this->get($connectionId);

        return $this->connectionFactory->create($this->connectionDTO);
    }

    /**
     * Load the default connection and its credentials.
     *
     * Populates the manager's current connection DTO with the default
     * connection configuration and its associated credentials.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function getDefaultConnection()
    {
        $connectionDTO = new ConnectionDTO();

        if (!$this->connectionRepository->getDefaultConnection($connectionDTO)) {
            throw new MagicalConnectionException(
                'Default connection not found.',
                'default_connection_not_found'
            );
        }

        if (!$this->connectionCredentialRepository->get($connectionDTO)) {
            throw new MagicalConnectionException(
                'Connection credentials not found.',
                'connection_credentials_not_found'
            );
        }
        $this->connectionDTO = $connectionDTO;
    }


}