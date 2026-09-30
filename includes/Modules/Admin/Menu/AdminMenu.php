<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Admin\Menu;


/**
 * Register and render the Magical Connection admin menu.
 *
 * Creates the main plugin menu in the WordPress administration area
 * and provides the registered hook suffix for page-specific asset loading.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class AdminMenu
{
    /**
     * WordPress hook suffix of the Magical Connection admin page.
     *
     * @since 1.0.0
     */
    private string $hook_suffix;

    /**
     * Register the admin menu hook.
     *
     * Hooks the menu creation method into the WordPress admin menu
     * registration lifecycle.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'create']);
    }

    /**
     * Create the main Magical Connection admin menu.
     *
     * Registers the plugin's top-level administration page and
     * stores the generated WordPress hook suffix for later use
     * by admin asset management.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function create(): void
    {
        $this->hook_suffix = add_menu_page(
            __('Magical Connection', MAGICAL_CONNECTION_TEXT_DOMAIN),
            __('Magical Connection', MAGICAL_CONNECTION_TEXT_DOMAIN),
            'manage_options',
            'magical-connection',
            [$this, 'render'],
            '',
            50
        );
    }

    /**
     * Get the WordPress hook suffix of the plugin admin page.
     *
     * @since 1.0.0
     *
     * @return string Registered admin page hook suffix.
     */
    public function getHookSuffix(): string
    {
        return $this->hook_suffix;
    }

    /**
     * Render the Magical Connection admin page.
     *
     * Delegates view rendering to the application's view loader.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function render(): void
    {
        mc_view()->render('index');
    }

}