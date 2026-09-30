<?php
declare(strict_types=1);

namespace MagicalConnection\Core\View;

use MagicalConnection\Core\Contracts\ServiceProviderInterface;

/**
 * Register the view services used by Magical Connection.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class ViewServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the view loader as a shared application service.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        mc_app()->singleton(
            ViewLoader::class,
            fn() => new ViewLoader(
                MAGICAL_CONNECTION_PATH . '/includes/Views'
            )
        );
    }
}