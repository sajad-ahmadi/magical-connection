<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Ajax;

use MagicalConnection\Modules\Connection\Services\ConnectionsManager;
use MagicalConnection\Modules\Connection\Services\ConnectionTestService;
use MagicalConnection\Support\AjaxNonce;
use Throwable;

/**
 * Handle AJAX requests for connection testing.
 *
 * Provides the AJAX endpoint used to test an existing
 * Magical Connection configuration.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionTestAjax
{
    /**
     * Service responsible for testing connection configurations.
     *
     * @since 1.0.0
     */
    private ConnectionTestService $connectionTestService;

    /**
     * Service responsible for retrieving connection configurations.
     *
     * @since 1.0.0
     */
    private ConnectionsManager $connectionsManager;

    /**
     * Create a new connection test AJAX handler.
     *
     * @since 1.0.0
     *
     * @param ConnectionTestService $connectionTestService Connection testing service.
     * @param ConnectionsManager     $connectionsManager    Connection management service.
     */
    public function __construct(ConnectionTestService $connectionTestService, ConnectionsManager $connectionsManager)
    {
        $this->connectionTestService = $connectionTestService;
        $this->connectionsManager = $connectionsManager;
    }

    /**
     * Register the connection testing AJAX endpoint.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp_ajax_magical_test_connection', [$this, 'test']);
    }

    /**
     * Test an existing connection through AJAX.
     *
     * Validates the request nonce, retrieves the requested connection,
     * executes the full connection test, and returns the test result
     * as a JSON response.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function test(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $connectionId = (int)($_POST['connection_id'] ?? 0);

            $connection = $this->connectionsManager->get($connectionId);
            $result = $this->connectionTestService->testFull($connection);

            if (!$result['success']) {
                wp_send_json_error($result);
            }

            wp_send_json_success($result);
        } catch (Throwable $e) {

            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'connection_test_failed',
            ]);
        }
    }
}