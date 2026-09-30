<?php
declare(strict_types=1);

namespace MagicalConnection\Core;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;
use MagicalConnection\Support\AjaxStream;

/**
 * Register core application services.
 *
 * Provides services shared across the core application lifecycle.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CoreServiceProvider implements ServiceProviderInterface
{
    /**
     * Register core application services.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            AjaxStream::class,
            fn() => new AjaxStream()
        );
    }
}