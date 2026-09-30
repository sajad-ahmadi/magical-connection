<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Services;

use MagicalConnection\Database\Database;
use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Modules\Connection\Repositories\ConnectionCredentialRepository;
use MagicalConnection\Modules\Connection\Repositories\ConnectionRepository;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage connection creation, updates, and deletion.
 *
 * Coordinates connection persistence and credential persistence as a
 * single application-level operation and protects related database
 * changes with transactions.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionService
{

    /**
     * Repository responsible for connection records.
     *
     * @since 1.0.0
     */
    private ConnectionRepository $connectionRepository;

    /**
     * Repository responsible for connection credentials.
     *
     * @since 1.0.0
     */
    private ConnectionCredentialRepository $credentialRepository;

    /**
     * Database abstraction used for transaction management.
     *
     * @since 1.0.0
     */
    private Database $database;

    /**
     * Create a new connection service.
     *
     * @since 1.0.0
     *
     * @param Database                       $database              Database abstraction.
     * @param ConnectionRepository            $connectionRepository  Connection repository.
     * @param ConnectionCredentialRepository  $credentialRepository  Credential repository.
     */
    public function __construct(Database $database, ConnectionRepository $connectionRepository, ConnectionCredentialRepository $credentialRepository)
    {
        $this->connectionRepository = $connectionRepository;
        $this->credentialRepository = $credentialRepository;
        $this->database = $database;
    }

    /**
     * Create a connection and its credentials atomically.
     *
     * Validates the connection configuration, creates the connection
     * record, stores its credentials, and commits both operations as
     * a single database transaction.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection configuration and credentials.
     *
     * @return int ID of the newly created connection.
     */
    public function create(ConnectionDTO $connection): int
    {
        $this->validate($connection);

        $this->database->beginTransaction();
        try {
            $connectionId = $this->connectionRepository->create($connection);

            if (!$connectionId) {
                throw new MagicalConnectionException(
                    'Failed to create connection.',
                    'connection_create_failed'
                );
            }

            $connection->setId($connectionId);
            $created = $this->credentialRepository->create($connection);

            if (!$created) {
                throw new MagicalConnectionException(
                    'Failed to create connection credentials.',
                    'connection_credentials_create_failed'
                );
            }

            $this->database->commit();
            return $connectionId;

        } catch (MagicalConnectionException $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    /**
     * Update a connection and its credentials atomically.
     *
     * Validates the connection configuration and updates both the
     * connection record and its associated credentials within the
     * same database transaction.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection configuration and credentials.
     *
     * @return bool True when both records are updated successfully.
     */
    public function update(ConnectionDTO $connection): bool
    {
        $this->validate($connection);

        $this->database->beginTransaction();
        try {
            $updated = $this->connectionRepository->update($connection);

            if (!$updated) {
                throw new MagicalConnectionException(
                    'Failed to update connection.',
                    'connection_update_failed'
                );
            }

            $credentialsUpdated = $this->credentialRepository->update($connection);
            if (!$credentialsUpdated) {
                throw new MagicalConnectionException(
                    'Failed to update connection credentials.',
                    'connection_credentials_update_failed'
                );
            }

            $this->database->commit();

            return true;
        } catch (MagicalConnectionException $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    /**
     * Delete a connection and its credentials atomically.
     *
     * Removes the associated credentials before deleting the connection
     * record so that a failed operation can be rolled back as a unit.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection to delete.
     *
     * @return bool True when both records are deleted successfully.
     */
    public function delete(ConnectionDTO $connection): bool
    {
        $this->database->beginTransaction();

        try {
            $deletedCredentials = $this->credentialRepository->delete($connection);
            if (!$deletedCredentials) {
                throw new MagicalConnectionException(
                    'Failed to delete connection credentials.',
                    'connection_credentials_delete_failed'
                );
            }

            $deletedConnection = $this->connectionRepository->delete($connection);
            if (!$deletedConnection) {
                throw new MagicalConnectionException(
                    'Failed to delete connection.',
                    'connection_delete_failed'
                );
            }
            $this->database->commit();
            return true;
        } catch (MagicalConnectionException $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    /**
     * Validate a connection configuration before persistence.
     *
     * Applies common validation rules and protocol-specific requirements
     * for FTP, HTTP, and HTTPS connections.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection configuration to validate.
     *
     * @return void
     */
    private function validate(ConnectionDTO $connection): void
    {
        if ($connection->getName() === '') {
            throw new MagicalConnectionException(
                'Connection name is required.',
                'connection_name_required'
            );
        }

        if (!in_array($connection->getProtocol(), ConnectionFactory::$PROTOCOLS, true)) {
            throw new MagicalConnectionException(
                'Invalid connection protocol.',
                'invalid_connection_protocol'
            );
        }

        if ($connection->getProtocol() === ConnectionFactory::$PROTOCOLS['ftp']) {
            if ($connection->getHost() === '') {
                throw new MagicalConnectionException(
                    'Host is required.',
                    'connection_host_required'
                );
            }
            if ($connection->getUsername() === '') {
                throw new MagicalConnectionException(
                    'FTP username is required.',
                    'ftp_username_required'
                );
            }

            if ($connection->getPassword() === '') {
                throw new MagicalConnectionException(
                    'FTP password is required.',
                    'ftp_password_required'
                );
            }

            if ($connection->getPort() < 1 || $connection->getPort() > 65535) {
                throw new MagicalConnectionException(
                    'Invalid port.',
                    'invalid_connection_port'
                );
            }
        }

        if (in_array($connection->getProtocol(), [ConnectionFactory::$PROTOCOLS['http'], ConnectionFactory::$PROTOCOLS['https']], true)) {
            if ($connection->getToken() === '') {
                throw new MagicalConnectionException(
                    'HTTP token is required.',
                    'http_token_required'
                );
            }
        }

        if ($connection->getDomain() === '') {
            throw new MagicalConnectionException(
                'Domain is required.',
                'connection_domain_required'
            );
        }

        if ($connection->getTimeout() < 1) {
            throw new MagicalConnectionException(
                'Timeout must be greater than zero.',
                'invalid_connection_timeout'
            );
        }
    }
}