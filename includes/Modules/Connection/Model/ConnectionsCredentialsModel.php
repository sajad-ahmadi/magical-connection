<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Model;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateConnectionsCredentialsTable;
use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;

/**
 * Manage stored credentials for connection records.
 *
 * Provides database operations for creating, retrieving, updating,
 * and deleting credentials associated with a connection.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionsCredentialsModel
{

    /**
     * Database table name for connection credentials.
     *
     * @since 1.0.0
     */
    private string $table;

    /**
     * Database abstraction used for persistence operations.
     *
     * @since 1.0.0
     */
    private Database $database;

    /**
     * Create a new connection credentials model.
     *
     * @since 1.0.0
     *
     * @param Database                         $database Database abstraction.
     * @param CreateConnectionsCredentialsTable $table    Credentials table definition.
     */
    public function __construct(Database $database, CreateConnectionsCredentialsTable $table)
    {
        $this->database = $database;
        $this->table = $table->tableName();
    }

    /**
     * Find credentials associated with a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection whose credentials should be retrieved.
     *
     * @return array|null Credential record, or null when not found.
     */
    public function find(ConnectionDTO $connection): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE `connection_id`=%s",
            $this->table,
            $connection->getId()
        );
        return $this->database->getRow($sql);
    }

    /**
     * Insert credentials for a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection containing credential data.
     *
     * @return int|false Inserted record ID, or false on failure.
     */
    public function insert(ConnectionDTO $connection)
    {
        return $this->database->insert(
            $this->table,
            [
                'connection_id' => $connection->getId(),
                'username'      => $connection->getUsername(),
                'password'      => $connection->getPassword(),
                'private_key'   => $connection->getPrivateKey(),
                'passphrase'    => $connection->getPassphrase(),
                'token'         => $connection->getToken()
            ]
        );
    }

    /**
     * Update credentials associated with a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection containing updated credential data.
     *
     * @return bool True when the record is updated successfully.
     */
    public function update(ConnectionDTO $connection): bool
    {
        return $this->database->update(
            $this->table,
            [
                'connection_id' => $connection->getId(),
                'username'      => $connection->getUsername(),
                'password'      => $connection->getPassword(),
                'private_key'   => $connection->getPrivateKey(),
                'passphrase'    => $connection->getPassphrase(),
                'token'         => $connection->getToken()
            ],
            [
                'id' => $connection->getCredentialsId()
            ]
        );
    }

    /**
     * Delete credentials associated with a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection whose credentials should be deleted.
     *
     * @return bool True when the credentials are deleted successfully.
     */
    public function delete(ConnectionDTO $connection): bool
    {
        return $this->database->delete($this->table, ['connection_id' => $connection->getId()]);
    }
}