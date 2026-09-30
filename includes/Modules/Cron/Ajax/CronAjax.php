<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Cron\Ajax;


use MagicalConnection\Modules\Cron\Services\CronService;
use MagicalConnection\Modules\Setting\Services\SettingManager;
use MagicalConnection\Support\AjaxNonce;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Handle AJAX requests for cron settings and execution.
 *
 * Provides endpoints for retrieving and updating cron configuration and
 * manually triggering the configured cron workflow.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class CronAjax
{
    /**
     * Manage persisted plugin settings.
     *
     * @var SettingManager
     *
     * @since 1.0.0
     */
    private SettingManager $settingManager;

    /**
     * Manage cron scheduling and execution.
     *
     * @var CronService
     *
     * @since 1.0.0
     */
    private CronService $cronService;

    /**
     * Create the cron AJAX handler.
     *
     * @param CronService    $cronService    Cron execution and scheduling service.
     * @param SettingManager $settingManager Plugin settings manager.
     *
     * @since 1.0.0
     */
    public function __construct(CronService $cronService, SettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
        $this->cronService = $cronService;
    }

    /**
     * Register cron-related AJAX actions.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        add_action('wp_ajax_magical_save_setting_cron_job', [$this, 'updateSettings']);
        add_action('wp_ajax_magical_get_setting_cron_job', [$this, 'getSettings']);
        add_action('wp_ajax_magical_cron_job_run', [$this, 'runCronJob']);
    }

    /**
     * Execute the cron workflow through AJAX.
     *
     * Verifies the AJAX nonce before executing the cron service and returns
     * the current cron settings together with the pending transfer state.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function runCronJob()
    {
        AjaxNonce::verifyOrFail();

        try {
            $this->cronService->run();

            wp_send_json_success([
                'message'              => __('Cron job executed successfully.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'cron'                 => $this->settingManager->get('cron'),
                'has_pending_transfer' => $this->cronService->hasPendingTransfer(),
            ]);
        } catch (MagicalConnectionException $e) {
            wp_send_json_error([
                'message' => __($e->getMessage(), MAGICAL_CONNECTION_TEXT_DOMAIN),
            ], 422);
        }

    }

    /**
     * Return the current cron settings and available schedules.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function getSettings(): void
    {
        AjaxNonce::verifyOrFail();

        wp_send_json_success([
            'message'              => __('Cron settings updated successfully.', MAGICAL_CONNECTION_TEXT_DOMAIN),
            'cron'                 => $this->settingManager->get('cron'),
            'list_time'            => $this->cronService->getSchedules(),
            'has_pending_transfer' => false,
        ]);
    }


    /**
     * Validate and persist cron settings received through AJAX.
     *
     * Missing values fall back to their currently stored settings.
     * Boolean and numeric values are normalized before persistence.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function updateSettings(): void
    {
        AjaxNonce::verifyOrFail();

        $cron = $_POST['cron'] ?? [];

        if (!is_array($cron) || empty($cron)) {
            wp_send_json_error([
                'message' => __('Invalid cron settings.', MAGICAL_CONNECTION_TEXT_DOMAIN),
            ], 422);
        }

        $settings = [
            'enabled'       => !empty($cron['enabled']),
            'interval'      => sanitize_text_field($cron['interval'] ?? $this->settingManager->get('cron.interval')),
            'method'        => sanitize_text_field($cron['method'] ?? $this->settingManager->get('cron.method')),
            'files_per_run' => absint($cron['files_per_run'] ?? $this->settingManager->get('cron.files_per_run')),
            'max_retries'   => absint($cron['max_retries'] ?? $this->settingManager->get('cron.max_retries')),
        ];

        $this->settingManager->updateGroup('cron', $settings);

        wp_send_json_success([
            'message' => __('Cron settings updated successfully.', MAGICAL_CONNECTION_TEXT_DOMAIN),
            'cron'    => $settings,
        ]);
    }
}