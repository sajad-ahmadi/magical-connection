<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner;

use MagicalConnection\Core\Container;
use MagicalConnection\Modules\Scanner\Ajax\ScannerAjax;
use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Modules\Scanner\Repositories\ScanRepository;
use MagicalConnection\Modules\Scanner\Services\MediaScannerService;
use MagicalConnection\Modules\Scanner\Services\TransferStatusResolver;

/**
 * Register and boot the scanner module.
 *
 * Registers scanner services in the application container and activates
 * the scanner AJAX handlers during the application boot phase.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ScannerServiceProvider
{
    /**
     * Register scanner services in the application container.
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            MediaScannerService::class,
            function (Container $container) {
                return new MediaScannerService(
                    $container->make(ScanRepository::class),
                    $container->make(ScanFileRepository::class),
                    $container->make(TransferStatusResolver::class)
                );
            }
        );
    }

    /**
     * Boot scanner integrations and register WordPress hooks.
     *
     * @return void
     */
    public function boot(): void
    {
        mc_app()->make(ScannerAjax::class)->register();
    }
}