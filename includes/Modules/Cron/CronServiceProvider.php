<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Cron;

use MagicalConnection\Modules\Cron\Ajax\CronAjax;
use MagicalConnection\Modules\Cron\Services\CronService;
use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;

/**
 * Register and boot the cron module.
 *
 * Registers cron services in the application container and boots the
 * WordPress cron and AJAX handlers.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class CronServiceProvider
{
    public function register(): void
    {
        mc_app()->singleton(
            CronService::class,
            function ($container) {
                return new CronService(
                    $container->make(ScanFileRepository::class)
                );
            }
        );
    }

    /**
     * Boot cron module hooks.
     *
     * Resolves the cron service and AJAX handler from the application
     * container and registers their WordPress hooks.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function boot(): void
    {
        mc_app()->make(CronService::class)->register();
        mc_app()->make(CronAjax::class)->register();
    }
}