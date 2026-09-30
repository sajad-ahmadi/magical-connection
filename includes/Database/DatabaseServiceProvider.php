<?php

declare(strict_types=1);

namespace MagicalConnection\Database;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;
use wpdb;

/**
 * Register database services.
 *
 * Registers the WordPress database instance and the application
 * database abstraction in the service container.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * Register database services.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            wpdb::class,
            function () {
                global $wpdb;

                return $wpdb;
            }
        );

        mc_app()->singleton(
            Database::class,
            function ($container) {
                return new Database(
                    $container->make(wpdb::class)
                );
            }
        );
    }
}