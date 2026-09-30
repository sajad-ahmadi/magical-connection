<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Setting\Ajax;


use MagicalConnection\Modules\Setting\Services\SettingManager;
use MagicalConnection\Support\AjaxNonce;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Handle AJAX requests for plugin settings.
 *
 * Provides endpoints for retrieving and saving grouped plugin settings
 * through the WordPress AJAX API.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class SettingAjax
{

    private SettingManager $settingManager;

    /**
     * Create the settings AJAX handler.
     *
     * @param SettingManager $settingManager Settings manager.
     */
    public function __construct(SettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * Register WordPress AJAX actions for settings.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp_ajax_magical_get_settings', [$this, 'getSettings']);
        add_action('wp_ajax_magical_save_settings', [$this, 'saveSettings']);
    }

    /**
     * Return settings for a requested setting group.
     *
     * The group key must be provided as an array through the AJAX request.
     *
     * @return void
     */
    public function getSettings(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $groupKey = [];
            if (isset($_POST['group_key']) && is_array($_POST['group_key'])) {
                $groupKey = $_POST['group_key'];
            } else {
                throw new MagicalConnectionException(
                    __('Invalid group key.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                    'invalid_group_key'
                );
            }
            wp_send_json_success($this->settingManager->gets($groupKey));
        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Save a group of plugin settings.
     *
     * Each submitted setting group is delegated to the setting manager,
     * which is responsible for applying the corresponding setting contract.
     *
     * @return void
     */
    public function saveSettings(): void
    {
        AjaxNonce::verifyOrFail();

        try {
            $group_setting = [];
            if (isset($_POST['group_setting']) && is_array($_POST['group_setting'])) {
                $group_setting = $_POST['group_setting'];
            } else {
                throw new MagicalConnectionException(
                    __('Invalid group key.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                    'invalid_group_key'
                );
            }
            foreach ($group_setting as $key => $value) {
                $this->settingManager->updateGroup($key, $value);
            }
            wp_send_json_success();
        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

}