<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

use MagicalConnection\Core\Application;
use MagicalConnection\Core\View\ViewLoader;
use MagicalConnection\Modules\Admin\Services\AccessService;
use MagicalConnection\Modules\Setting\Services\SettingManager;
use MagicalConnection\Support\AjaxStream;

if (!function_exists('magical')) {

    /**
     * Get the Magical Connection application instance.
     *
     * @since 1.0.0
     *
     * @return Application Application instance.
     */
    function magical(): Application
    {
        return Application::instance();
    }
}

if (!function_exists('mc_app')) {

    /**
     * Get the application service container.
     *
     * @since 1.0.0
     *
     * @return object Application service container.
     */
    function mc_app(): object
    {
        return magical()->container;
    }
}

if (!function_exists('mc_setting')) {

    /**
     * Get the application settings manager.
     *
     * @since 1.0.0
     *
     * @return SettingManager Settings manager instance.
     */
    function mc_setting(): SettingManager
    {
        return mc_app()->make(SettingManager::class);
    }
}

if (!function_exists('mc_view')) {

    /**
     * Get the application view loader.
     *
     * @since 1.0.0
     *
     * @return ViewLoader View loader instance.
     */
    function mc_view(): ViewLoader
    {
        return mc_app()->make(ViewLoader::class);
    }
}

if (!function_exists('mc_can_access')) {

    /**
     * Determine whether the current user can access the plugin.
     *
     * @since 1.0.0
     *
     * @return bool True when the current user has access.
     */
    function mc_can_access(): bool
    {
        return mc_app()->make(AccessService::class)->canAccess();
    }
}

if (!function_exists('mc_stream')) {

    /**
     * Get the AJAX stream service.
     *
     * @since 1.0.0
     *
     * @return AjaxStream AJAX stream service instance.
     */
    function mc_stream(): AjaxStream
    {
        return mc_app()->make(AjaxStream::class);
    }
}