<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Repositories;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Modules\Connection\Model\ConnectionsModel;

/**
 * Provide repository operations for connection records.
 *
 * Coordinates connection persistence and converts database records
 * into ConnectionDTO instances used by the application layer.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class ConnectionRepository
{
    /**
     * Model responsible for connection persistence.
     *
     * @since 1.0.0
     */
    private ConnectionsModel $connectionsModel;

    /**
     * Create a new connection repository.
     *
     * @since 1.0.0
     *
     * @param ConnectionsModel $connectionsModel Connection persistence model.
     */
    public function __construct(ConnectionsModel $connectionsModel)
    {
        $this->connectionsModel = $connectionsModel;
    }

    /**
     * Load a connection into a DTO.
     *
     * Retrieves the connection identified by the DTO and populates
     * the same DTO with the stored connection data.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $dto Connection DTO containing the connection ID.
     *
     * @return bool True when the connection is found and loaded.
     */
    public function get(ConnectionDTO $dto): bool
    {
        $connection = $this->connectionsModel->find($dto);

        if ($connection === null) {
            return false;
        }

        $this->fillDTO($dto, $connection);

        return true;
    }

    /**
     * Load the default connection into a DTO.
     *
     * Retrieves the connection marked as the default connection
     * and populates the provided DTO with its stored data.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $dto DTO to populate with the default connection.
     *
     * @return bool True when a default connection is found and loaded.
     */
    public function getDefaultConnection(ConnectionDTO $dto): bool
    {
        $connection = $this->connectionsModel->findDefaultConnection();

        if ($connection === null) {
            return false;
        }

        $this->fillDTO($dto, $connection);

        return true;
    }

    /**
     * Retrieve all stored connections.
     *
     * When requested, returns the raw database records. Otherwise,
     * each record is converted into a ConnectionDTO instance.
     *
     * @since 1.0.0
     *
     * @param bool $is_array Whether to return raw database records.
     *
     * @return array Connection records.
     */
    public function getConnections(bool $is_array = false): array
    {
        $list = [];
        $connections = $this->connectionsModel->getAll();

        if ($is_array) {
            return $connections;
        }

        foreach ($connections as $connection) {
            $dto = new ConnectionDTO();

            $this->fillDTO($dto, $connection);

            $list[] = $dto;
        }

        return $list;
    }

    /**
     * Create a new connection record.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection data to persist.
     *
     * @return int|false Inserted connection ID, or false on failure.
     */
    public function create(ConnectionDTO $connectionDTO)
    {
        return $this->connectionsModel->insert($connectionDTO);
    }

    /**
     * Update an existing connection record.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection data to update.
     *
     * @return bool True when the connection is updated successfully.
     */
    public function update(ConnectionDTO $connectionDTO): bool
    {
        return $this->connectionsModel->update($connectionDTO);
    }

    /**
     * Delete a connection record.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection to delete.
     *
     * @return bool True when the connection is deleted successfully.
     */
    public function delete(ConnectionDTO $connectionDTO): bool
    {
        return $this->connectionsModel->delete($connectionDTO);
    }

    /**
     * Populate a connection DTO from a database record.
     *
     * Converts database scalar values to the types expected by
     * ConnectionDTO and copies the calculated transfer statistics
     * returned by the connection query.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $dto              DTO to populate.
     * @param array $connection Database connection record.
     *
     * @return void
     */
    private function fillDTO(ConnectionDTO &$dto, array $connection): void
    {
        $dto->setId((int)$connection['id']);
        $dto->setName((string)$connection['name']);
        $dto->setDomain((string)$connection['domain']);
        $dto->setHost((string)$connection['host']);
        $dto->setProtocol((string)$connection['protocol']);
        $dto->setPort((int)$connection['port']);
        $dto->setBasePath((string)$connection['base_path']);
        $dto->setTimeout((int)$connection['timeout']);
        $dto->setStatus((int)$connection['status']);
        $dto->setTransferredFiles((int)$connection['transferred_files']);
        $dto->setPassiveMod((int)$connection['passive_mod']);
        $dto->setCreatedAt($connection['created_at']);
        $dto->setUpdatedAt($connection['updated_at']);
    }
}