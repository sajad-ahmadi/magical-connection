<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\AutoTransfer;

use MagicalConnection\Modules\AutoTransfer\Repositories\TransferQueueRepository;
use MagicalConnection\Modules\AutoTransfer\Services\AutoTransferService;
use MagicalConnection\Modules\Media\Service\MediaTransferService;
use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Modules\Setting\Repositories\SettingRepository;

/**
 * Register automatic transfer services.
 *
 * Registers the services required by the automatic media transfer
 * module in the application service container.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class AutoTransferServiceProvider
{

    /**
     * Register automatic transfer services.
     *
     * Registers the automatic transfer service as a singleton so
     * the same service instance is reused during the application lifecycle.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            AutoTransferService::class,
            function ($container) {
                return new AutoTransferService(
                    $container->make(MediaTransferService::class),
                    $container->make(SettingRepository::class),
                    $container->make(TransferQueueRepository::class),
                    $container->make(ScanFileRepository::class)
                );
            }
        );
    }
}