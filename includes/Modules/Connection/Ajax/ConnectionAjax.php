<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Ajax;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Modules\Connection\Services\ConnectionService;
use MagicalConnection\Modules\Connection\Services\ConnectionsManager;
use MagicalConnection\Modules\Connection\Services\ConnectionTestService;
use MagicalConnection\Support\AjaxNonce;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Handle AJAX requests for connection management.
 *
 * Provides AJAX endpoints for creating, retrieving, updating, deleting,
 * and testing Magical Connection connection configurations.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionAjax
{
    /**
     * Service responsible for connection persistence operations.
     *
     * @since 1.0.0
     */
    private ConnectionService $connectionService;

    /**
     * Service responsible for testing connection configurations.
     *
     * @since 1.0.0
     */
    private ConnectionTestService $connectionTestService;

    /**
     * Service responsible for retrieving and managing connections.
     *
     * @since 1.0.0
     */
    private ConnectionsManager $connectionsManager;

    /**
     * Create a new connection AJAX handler.
     *
     * @since 1.0.0
     *
     * @param ConnectionService     $connectionService     Connection persistence service.
     * @param ConnectionTestService $connectionTestService Connection testing service.
     * @param ConnectionsManager    $connectionsManager    Connection management service.
     */
    public function __construct(ConnectionService $connectionService, ConnectionTestService $connectionTestService, ConnectionsManager $connectionsManager)
    {
        $this->connectionService = $connectionService;
        $this->connectionTestService = $connectionTestService;
        $this->connectionsManager = $connectionsManager;
    }

    /**
     * Register connection-related AJAX endpoints.
     *
     * Registers endpoints for connection creation, retrieval,
     * updating, deletion, and listing.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp_ajax_magical_create_connection', [$this, 'create']);
        add_action('wp_ajax_magical_update_connection', [$this, 'update']);
        add_action('wp_ajax_magical_delete_connection', [$this, 'delete']);

        add_action('wp_ajax_magical_get_connection_single', [$this, 'get']);

        add_action('wp_ajax_magical_get_connections', [$this, 'getConnections']);
    }

    /**
     * Return a single connection through AJAX.
     *
     * Validates the request nonce and connection ID before retrieving
     * the requested connection.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $connection_id = (int)($_POST['connection_id'] ?? 0);
            if ($connection_id <= 0) {
                throw new MagicalConnectionException(
                    __('Invalid connection ID.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                    'invalid_connection_id'
                );
            }

            $connection = $this->connectionsManager->get($connection_id)->toArray();

            $message = empty($connection) ? __('No connections found.', MAGICAL_CONNECTION_TEXT_DOMAIN) : "";

            wp_send_json_success(
                [
                    'connection' => $connection,
                    'message'    => $message
                ]
            );
        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Return all available connections through AJAX.
     *
     * Validates the request nonce before retrieving the connection list.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function getConnections(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $connections = $this->connectionsManager->getConnections(true);
            $message = empty($connections) ? __('No connections found.', MAGICAL_CONNECTION_TEXT_DOMAIN) : "";

            wp_send_json_success(
                [
                    'connections' => $connections,
                    'message'     => $message
                ]
            );
        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a new connection through AJAX.
     *
     * Builds a connection DTO from the request, verifies the connection
     * configuration, and persists it when the connection test succeeds.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function create(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $connectionId = 0;

            $dto = $this->makeDTO();

            $result_test = $this->connectionTestService->testFull($dto);
            if ($result_test['success'] === true()) {
                $connectionId = $this->connectionService->create($dto);
            } else {
                throw new MagicalConnectionException(
                    'Connection test failed',
                    'connection_test_failed'
                );
            }

            wp_send_json_success(
                [
                    'connection_id'   => $connectionId,
                    'test_connection' => $result_test
                ]
            );

        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update an existing connection through AJAX.
     *
     * Loads the existing connection, applies submitted fields,
     * verifies the resulting configuration, and persists it when
     * the connection test succeeds.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update(): void
    {
        AjaxNonce::verifyOrFail();

        try {

            $connection_id = (int)($_POST['connection_id'] ?? 0);
            if ($connection_id <= 0) {
                throw new MagicalConnectionException(
                    __('Invalid connection ID.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                    'invalid_connection_id'
                );
            }

            $dto = $this->connectionsManager->get($connection_id);
            $this->fillDTO($dto);

            $result_test = $this->connectionTestService->testFull($dto);
            if ($result_test['success'] === false) {
                $this->connectionService->update($dto);
            } else {
                throw new MagicalConnectionException(
                    'Connection test failed',
                    'connection_test_failed'
                );
            }

            wp_send_json_success([
                'connection_id'   => $connection_id,
                'test_connection' => $result_test,
                'message'         => __('Connection updated successfully.', MAGICAL_CONNECTION_TEXT_DOMAIN),
            ]);

        } catch (MagicalConnectionException $e) {

            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete an existing connection through AJAX.
     *
     * Validates the connection ID, retrieves the connection DTO,
     * and removes the connection through the connection service.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function delete(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $connection_id = (int)($_POST['connection_id'] ?? 0);
            if ($connection_id <= 0) {
                throw new MagicalConnectionException(
                    __('Invalid connection ID.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                    'invalid_connection_id'
                );
            }

            $dto = $this->connectionsManager->get($connection_id);
            $this->connectionService->delete($dto);

            wp_send_json_success([
                'connection_id' => $connection_id,
                'message'       => __('Connection deleted successfully.', MAGICAL_CONNECTION_TEXT_DOMAIN),
            ]);

        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build a connection DTO from the current AJAX request.
     *
     * Sanitizes textual connection fields while preserving sensitive
     * credential values such as passwords, private keys, passphrases,
     * and tokens without text-field normalization.
     *
     * @since 1.0.0
     *
     * @return ConnectionDTO Connection DTO populated from the request.
     */
    private function makeDTO(): ConnectionDTO
    {

        $dto = new ConnectionDTO();

        $dto->setName(sanitize_text_field($_POST['name'] ?? ''));
        $dto->setProtocol(sanitize_text_field($_POST['protocol'] ?? ''));
        $dto->setHost(sanitize_text_field($_POST['host'] ?? ''));
        $dto->setDomain(sanitize_text_field($_POST['domain'] ?? ''));
        $dto->setPort((int)($_POST['port'] ?? 21));
        $dto->setBasePath(sanitize_text_field($_POST['base_path'] ?? ''));
        $dto->setTimeout((int)($_POST['timeout'] ?? 60));
        $dto->setPassiveMod((int)($_POST['passive_mod'] ?? 0));

        // Credentials
        $dto->setUsername(sanitize_text_field($_POST['username'] ?? ''));
        $dto->setPassword($_POST['password'] ?? '');
        $dto->setPrivateKey($_POST['private_key'] ?? '');
        $dto->setPassphrase($_POST['passphrase'] ?? '');
        $dto->setToken($_POST['token'] ?? '');

        return $dto;
    }

    /**
     * Apply submitted connection fields to an existing DTO.
     *
     * Only fields explicitly included in the request are updated,
     * allowing partial connection updates while preserving existing
     * values for omitted fields.
     *
     * @since 1.0.0
     *
     * @param ConnectionDTO $dto Existing connection DTO to update.
     *
     * @return ConnectionDTO Updated connection DTO.
     */
    private function fillDTO(ConnectionDTO $dto): ConnectionDTO
    {
        if (isset($_POST['name'])) {
            $dto->setName(sanitize_text_field($_POST['name']));
        }

        if (isset($_POST['protocol'])) {
            $dto->setProtocol(sanitize_text_field($_POST['protocol']));
        }

        if (isset($_POST['host'])) {
            $dto->setHost(sanitize_text_field($_POST['host']));
        }

        if (isset($_POST['domain'])) {
            $dto->setDomain(sanitize_text_field($_POST['domain']));
        }

        if (isset($_POST['port'])) {
            $dto->setPort((int)$_POST['port']);
        }

        if (isset($_POST['base_path'])) {
            $path = sanitize_text_field($_POST['base_path']);
            $path = '/' . trim($path, '/') . '/';
            $dto->setBasePath($path);
        }

        if (isset($_POST['timeout'])) {
            $dto->setTimeout((int)$_POST['timeout']);
        }

        if (isset($_POST['passive_mod'])) {
            $dto->setPassiveMod((int)$_POST['passive_mod']);
        }

        if (isset($_POST['username'])) {
            $dto->setUsername(sanitize_text_field($_POST['username']));
        }

        if (isset($_POST['password'])) {
            $dto->setPassword($_POST['password']);
        }

        if (isset($_POST['private_key'])) {
            $dto->setPrivateKey($_POST['private_key']);
        }

        if (isset($_POST['passphrase'])) {
            $dto->setPassphrase($_POST['passphrase']);
        }

        if (isset($_POST['token'])) {
            $dto->setToken($_POST['token']);
        }

        return $dto;
    }
}