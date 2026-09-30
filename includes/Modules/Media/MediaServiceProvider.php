<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Media;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;
use MagicalConnection\Modules\Media\Action\MediaTransferAction;
use MagicalConnection\Modules\Media\Ajax\MediaTransferAjax;
use MagicalConnection\Modules\Media\Repositories\MediaTransferRepository;
use MagicalConnection\Modules\Media\Service\MediaUrlService;

/**
 * Register and boot the media module services.
 *
 * Registers media-related services in the application container and
 * attaches the module's WordPress actions, filters, and AJAX handlers.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaServiceProvider implements ServiceProviderInterface
{
    /**
     * Register media module services.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        mc_app()->singleton(
            MediaUrlService::class,
            function ($container) {
                return new MediaUrlService(
                    $container->make(
                        MediaTransferRepository::class
                    )
                );
            }
        );
    }

    /**
     * Boot the media module.
     *
     * Registers WordPress actions, filters, and AJAX handlers after
     * all service providers have registered their container bindings.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function boot(): void
    {
        mc_app()->make(MediaTransferAction::class)->register();
        mc_app()->make(MediaTransferAjax::class)->register();
    }

}