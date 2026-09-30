<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;
use MagicalConnection\Modules\Connection\Ajax\ConnectionAjax;
use MagicalConnection\Modules\Connection\Ajax\ConnectionTestAjax;

/**
 * Register and boot the connection module.
 *
 * Registers connection-related WordPress hooks after the application
 * services have been registered and the container is ready.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ConnectionProvider implements ServiceProviderInterface
{

    /**
     * Register connection module services.
     *
     * Service bindings are currently provided by the application's
     * existing providers, so no bindings are required here.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register(): void
    {

    }

    /**
     * Boot connection module hooks.
     *
     * Resolves the connection AJAX handlers from the application container
     * and registers their WordPress AJAX actions.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function boot(): void
    {
        mc_app()->make(ConnectionAjax::class)->register();
        mc_app()->make(ConnectionTestAjax::class)->register();
    }
}