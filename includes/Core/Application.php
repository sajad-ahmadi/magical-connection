<?php
declare(strict_types=1);

namespace MagicalConnection\Core;

use MagicalConnection\Core\View\ViewServiceProvider;
use MagicalConnection\Database\DatabaseServiceProvider;
use MagicalConnection\Modules\Admin\AdminServiceProvider;
use MagicalConnection\Modules\Connection\ConnectionProvider;
use MagicalConnection\Modules\Cron\CronServiceProvider;
use MagicalConnection\Modules\Media\MediaServiceProvider;
use MagicalConnection\Modules\Scanner\ScannerServiceProvider;
use MagicalConnection\Modules\Setting\SettingServiceProvider;

defined('ABSPATH') || exit;

/**
 * Manage the Magical Connection application instance and service providers.
 *
 * Initializes the application container, registers core services,
 * and loads the configured service providers during application boot.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Application
{
    /**
     * The current application instance.
     *
     * @since 1.0.0
     *
     * @var Application|null
     */
    protected static ?Application $instance = null;

    /**
     * Application service container.
     *
     * @since 1.0.0
     */
    public Container $container;

    /**
     * Get the current application instance.
     *
     * @since 1.0.0
     *
     * @return Application Application instance.
     */
    public static function instance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Initialize the application container.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $this->container = new Container();
    }


    /**
     * Boot the application and load its service providers.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerBaseServices();
        $this->loadProviders();
    }

    /**
     * Register the services required by the application core.
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function registerBaseServices(): void
    {
        $this->container->bind(
            Container::class,
            fn() => $this->container
        );
    }

    /**
     * Load and initialize the configured service providers.
     *
     * Providers are registered before their optional boot methods
     * are executed.
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function loadProviders(): void
    {
        $providers = [
            DatabaseServiceProvider::class,
            SettingServiceProvider::class,
            AdminServiceProvider::class,
            ConnectionProvider::class,
            MediaServiceProvider::class,
            ScannerServiceProvider::class,
            CronServiceProvider::class,
            ViewServiceProvider::class,
            CoreServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $instance = $this->container->make($provider);
            $instance->register();
            if (method_exists($instance, 'boot')) {
                $instance->boot();
            }
        }
    }
}