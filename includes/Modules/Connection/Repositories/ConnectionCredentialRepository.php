<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Repositories;


use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Modules\Connection\Model\ConnectionsCredentialsModel;

/**
 * Provide repository operations for connection credentials.
 *
 * Coordinates credential persistence and hydration between the
 * connection DTO and the connection credentials model.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class ConnectionCredentialRepository
{
    /**
     * Model responsible for connection credential persistence.
     *
     * @since 1.0.0
     */
    private ConnectionsCredentialsModel $connectionsCredentialsModel;

    /**
     * Create a new connection credential repository.
     *
     * @since 1.0.0
     *
     * @param ConnectionsCredentialsModel $connectionsCredentialsModel
     *        Connection credentials persistence model.
     */
    public function __construct(ConnectionsCredentialsModel $connectionsCredentialsModel)
    {
        $this->connectionsCredentialsModel = $connectionsCredentialsModel;
    }

    /**
     * Load stored credentials into a connection DTO.
     *
     * Updates the provided DTO with the credentials associated with
     * its connection ID when a credential record exists.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection DTO to hydrate.
     *
     * @return bool True when credentials are found and loaded.
     */
    public function get(ConnectionDTO &$connectionDTO): bool
    {
        $connection = $this->connectionsCredentialsModel->find($connectionDTO);

        if ($connection === null) {
            return false;
        }

        $connectionDTO->setUsername($connection["username"]);
        $connectionDTO->setPassword($connection["password"]);
        $connectionDTO->setPrivateKey($connection["private_key"]);
        $connectionDTO->setPassphrase($connection["passphrase"]);
        $connectionDTO->setToken($connection["token"]);

        return true;
    }

    /**
     * Create a credential record for a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection containing credential data.
     *
     * @return int|false Inserted credential record ID, or false on failure.
     */
    public function create(ConnectionDTO $connectionDTO)
    {
        return $this->connectionsCredentialsModel->insert($connectionDTO);
    }

    /**
     * Update credentials associated with a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection containing updated credentials.
     *
     * @return bool True when the credentials are updated successfully.
     */
    public function update(ConnectionDTO $connectionDTO)
    {
        return $this->connectionsCredentialsModel->update($connectionDTO);
    }

    /**
     * Delete credentials associated with a connection.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $connectionDTO Connection whose credentials should be deleted.
     *
     * @return bool True when the credentials are deleted successfully.
     */
    public function delete(ConnectionDTO $connectionDTO)
    {
        return $this->connectionsCredentialsModel->delete($connectionDTO);
    }

}