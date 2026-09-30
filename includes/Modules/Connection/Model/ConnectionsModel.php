<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Model;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateConnectionsTable;
use MagicalConnection\Database\Migrations\CreateMediaTransfersTable;
use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;

/**
 * Manage connection records and their transfer statistics.
 *
 * Provides database operations for retrieving, creating, updating,
 * and deleting connection configurations, including the number of
 * successfully transferred media files associated with each connection.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionsModel
{
    /**
     * Database table name for connection records.
     *
     * @since 1.0.0
     */
    private string $table;

    /**
     * Database table name for media transfer records.
     *
     * @since 1.0.0
     */
    private string $media_transfer_table;

    /**
     * Database abstraction used for persistence operations.
     *
     * @since 1.0.0
     */
    private Database $database;

    /**
     * Create a new connections model.
     *
     * @since 1.0.0
     *
     * @param Database                   $database      Database abstraction.
     * @param CreateConnectionsTable     $table         Connections table definition.
     * @param CreateMediaTransfersTable $transfersTable Media transfers table definition.
     */
    public function __construct(Database $database, CreateConnectionsTable $table, CreateMediaTransfersTable $transfersTable)
    {
        $this->database = $database;
        $this->table = $table->tableName();
        $this->media_transfer_table = $transfersTable->tableName();
    }

    /**
     * Find a connection and its transferred file count.
     *
     * Counts media transfer records with a transferred status for
     * the requested connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection to retrieve.
     *
     * @return array|null Connection record, or null when not found.
     */
    public function find(ConnectionDTO $connection): ?array
    {
        $sql = $this->database->prepare(
            "SELECT c.*,
        COUNT(mt.id) AS transferred_files
    FROM %i AS c
    LEFT JOIN %i AS mt
        ON mt.connection_id = c.id
        AND mt.status = %s
    WHERE c.id = %d
    GROUP BY c.id",
            $this->table,
            $this->media_transfer_table,
            'transferred',
            $connection->getId()
        );
        return $this->database->getRow($sql);
    }

    /**
     * Find the default connection.
     *
     * The default connection is identified by the active/default
     * status value stored in the connection record.
     *
     * @since 1.0.0
     *
     * @return array|null Default connection, or null when none exists.
     */
    public function findDefaultConnection(): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE `status`=%s ",
            $this->table,
            1
        );
        return $this->database->getRow($sql);
    }

    /**
     * Retrieve all connections with their transferred file counts.
     *
     * The transferred file count includes only media transfer records
     * whose status is `transferred`.
     *
     * @since 1.0.0
     *
     * @return array Connection records.
     */
    public function getAll(): ?array
    {
        $sql = $this->database->prepare("SELECT 
        c.*,
        COUNT(mt.id) AS transferred_files
    FROM %i AS c
    LEFT JOIN %i AS mt
        ON mt.connection_id = c.id
        AND mt.status = %s
    GROUP BY c.id",
            $this->table,
            $this->media_transfer_table,
            'transferred');
        return $this->database->getResults($sql);
    }

    /**
     * Insert a new connection record.
     *
     * Credential fields are intentionally excluded because they are
     * persisted separately by the connection credentials model.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection configuration to insert.
     *
     * @return int|false Inserted record ID, or false on failure.
     */
    public function insert(ConnectionDTO $connection)
    {
        return $this->database->insert(
            $this->table,
            [
                'name'        => $connection->getName(),
                'protocol'    => $connection->getProtocol(),
                'domain'      => $connection->getDomain(),
                'host'        => $connection->getHost(),
                'port'        => $connection->getPort(),
                'base_path'   => $connection->getBasePath(),
                'timeout'     => $connection->getTimeout(),
                'status'      => $connection->getStatus(),
                'passive_mod' => $connection->getPassiveMod(),
            ]
        );
    }

    /**
     * Update an existing connection record.
     *
     * Credential fields are intentionally excluded because they are
     * persisted separately by the connection credentials model.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection configuration to update.
     *
     * @return bool True when the record is updated successfully.
     */
    public function update(ConnectionDTO $connection): bool
    {
        return $this->database->update(
            $this->table,
            [
                'name'        => $connection->getName(),
                'protocol'    => $connection->getProtocol(),
                'domain'      => $connection->getDomain(),
                'host'        => $connection->getHost(),
                'port'        => $connection->getPort(),
                'base_path'   => $connection->getBasePath(),
                'timeout'     => $connection->getTimeout(),
                'status'      => $connection->getStatus(),
                'passive_mod' => $connection->getPassiveMod(),
            ],
            [
                'id' => $connection->getId()
            ]
        );
    }

    /**
     * Delete a connection record.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connection Connection to delete.
     *
     * @return bool True when the record is deleted successfully.
     */
    public function delete(ConnectionDTO $connection): bool
    {
        return $this->database->delete($this->table, ['id' => $connection->getId()]);
    }
}