<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Admin;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;
use MagicalConnection\Modules\Admin\Menu\AdminAssets;
use MagicalConnection\Modules\Admin\Menu\AdminMenu;
use MagicalConnection\Modules\Admin\Services\AccessService;

/**
 * Register and initialize admin services.
 *
 * Registers the services required by the Magical Connection
 * administration area and initializes admin menu, asset, and
 * access-related hooks.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class AdminServiceProvider implements ServiceProviderInterface
{

    /**
     * Register admin services and hooks.
     *
     * Registers the admin menu and access services in the
     * application container and initializes the admin menu
     * and asset registration.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            AdminMenu::class,
            fn () => new AdminMenu()
        );

        mc_app()->singleton(
            AdminAssets::class,
            fn ($container) => new AdminAssets(
                $container->make(AdminMenu::class)
            )
        );

        mc_app()->singleton(
            AccessService::class,
            fn () => new AccessService()
        );
    }

    /**
     * Register admin access-related AJAX hooks.
     *
     * Initializes the access service after application services
     * have been registered.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function boot ():void {
        mc_app()->make(AccessService::class)->ajax();
        mc_app()->make(AdminMenu::class)->register();
        mc_app()->make(AdminAssets::class)->register();
    }
}