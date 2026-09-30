<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Admin\Services;

use MagicalConnection\Support\AjaxNonce;

/**
 * Manage access rules and AJAX endpoints for the admin area.
 *
 * Provides permission checks based on the configured allowed roles
 * and registers the AJAX endpoint used to retrieve available WordPress
 * roles.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class AccessService
{

    /**
     * Register AJAX endpoints related to access management.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function ajax()
    {
        add_action('wp_ajax_magical_get_available_roles', [$this, 'getAvailableRoles']);
    }


    /**
     * Determine whether the current user can access the plugin.
     *
     * Administrators are granted access automatically. Other users
     * must have at least one role included in the configured
     * `permissions.allowed_roles` setting.
     *
     * @since 1.0.0
     *
     * @return bool True when the current user is allowed to access the plugin.
     */
    public function canAccess(): bool
    {
        if (current_user_can('administrator')) {
            return true;
        }

        $allowedRoles = mc_setting()->get('permissions.allowed_roles');

        if (!is_array($allowedRoles)) {
            return false;
        }

        $user = wp_get_current_user();

        return !empty(array_intersect($user->roles, $allowedRoles));
    }

    /**
     * Return the available WordPress roles through AJAX.
     *
     * Validates the Magical Connection AJAX nonce before returning
     * the registered WordPress roles as a JSON success response.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function getAvailableRoles()
    {
        AjaxNonce::verifyOrFail();
        wp_send_json_success(wp_roles()->roles);
    }
}