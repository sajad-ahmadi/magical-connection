<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Setting;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;
use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateSettingsTable;
use MagicalConnection\Modules\Setting\Ajax\SettingAjax;
use MagicalConnection\Modules\Setting\Model\SettingModel;
use MagicalConnection\Modules\Setting\Repositories\SettingRepository;
use MagicalConnection\Modules\Setting\Services\SettingManager;

/**
 * Register and boot the setting module.
 *
 * Registers the setting model, repository, and manager with the application
 * container and attaches the WordPress AJAX handlers for settings.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class SettingServiceProvider implements ServiceProviderInterface
{
    /**
     * Register setting module services.
     *
     * Database persistence services are registered during the registration
     * phase so they are available before provider boot methods execute.
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            SettingModel::class,
            function ($container) {
                return new SettingModel(
                    $container->make(Database::class),
                    $container->make(CreateSettingsTable::class)
                );
            }
        );

        mc_app()->singleton(
            SettingRepository::class,
            function ($container) {
                return new SettingRepository(
                    $container->make(SettingModel::class)
                );
            }
        );

        mc_app()->singleton(
            SettingManager::class,
            function ($container) {
                return new SettingManager(
                    $container->make(SettingRepository::class)
                );
            }
        );
    }

    /**
     * Boot the setting module.
     *
     * Registers WordPress AJAX handlers after all service providers have
     * completed their registration phase.
     *
     * @return void
     */
    public function boot(): void
    {
        mc_app()->make(SettingAjax::class)->register();
    }
}