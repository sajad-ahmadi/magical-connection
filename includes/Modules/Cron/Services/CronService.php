<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Cron\Services;

use MagicalConnection\Modules\AutoTransfer\Services\AutoTransferService;
use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage WordPress cron scheduling and cron execution.
 *
 * Coordinates the plugin cron lifecycle, custom WordPress schedules,
 * configured execution method, and pending transfer checks.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CronService
{
    /**
     * WordPress cron hook used by the plugin.
     *
     * @since 1.0.0
     */
    private const HOOK = 'magical_connection_cron';

    /**
     * Current cron settings.
     *
     * @since 1.0.0
     * @var array
     *
     */
    private array $setting_cron = [];

    /**
     * Repository used to inspect pending scan transfers.
     *
     * @since 1.0.0
     * @var ScanFileRepository
     *
     */
    private ScanFileRepository $scanFileRepository;

    /**
     * Create the cron service.
     *
     * @since 1.0.0
     * @param ScanFileRepository $scanFileRepository Scan file repository.
     *
     */
    public function __construct(ScanFileRepository $scanFileRepository)
    {
        $this->scanFileRepository = $scanFileRepository;
    }

    /**
     * Register the cron initialization hook.
     *
     * @since 1.0.0
     * @return void
     *
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerCron']);
    }

    /**
     * Register cron schedules and the plugin cron action.
     *
     * The current cron configuration is evaluated and the corresponding
     * WordPress event is scheduled when WordPress cron execution is enabled.
     *
     * @since 1.0.0
     * @return void
     *
     */
    public function registerCron(): void
    {
        add_filter('cron_schedules', [$this, 'addSchedule']);
        add_action(self::HOOK, [$this, 'run']);

        $this->schedule();
    }

    /**
     * Execute the configured cron workflow.
     *
     * The execution is skipped by exception when cron is disabled.
     * Transfer processing is limited by the configured files-per-run value.
     *
     * @since 1.0.0
     * @return void
     *
     */
    public function run(): void
    {
        $this->loadSettings();

        if (!$this->isEnabled()) {
            throw new MagicalConnectionException(
                'Cron job is not enabled',
                'cron_job_not_enabled'
            );
        }

        for ($i = 1; $i <= $this->setting_cron['files_per_run']; $i++) {
            mc_app()->make(AutoTransferService::class)->run();
        }
    }

    /**
     * Add plugin-specific WordPress cron schedules.
     *
     * @since 1.0.0
     * @param array $schedules Existing schedules.
     *
     * @return array Updated schedules.
     *
     */
    public function addSchedule(array $schedules): array
    {
        $schedules['magical_connection_1_minute'] = [
            'interval' => 60,
            'display'  => __('Every 1 Minute', 'magical-connection'),
        ];

        $schedules['magical_connection_5_minutes'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display'  => __('Every 5 Minutes', 'magical-connection'),
        ];

        $schedules['magical_connection_10_minutes'] = [
            'interval' => 10 * MINUTE_IN_SECONDS,
            'display'  => __('Every 10 Minutes', 'magical-connection'),
        ];

        $schedules['magical_connection_15_minutes'] = [
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => __('Every 15 Minutes', 'magical-connection'),
        ];

        $schedules['magical_connection_30_minutes'] = [
            'interval' => 30 * MINUTE_IN_SECONDS,
            'display'  => __('Every 30 Minutes', 'magical-connection'),
        ];

        $schedules['magical_connection_1_hour'] = [
            'interval' => HOUR_IN_SECONDS,
            'display'  => __('Every 1 Hour', 'magical-connection'),
        ];

        return $schedules;
    }

    /**
     * Get all schedules supported by the plugin.
     *
     * @since 1.0.0
     * @return array Available schedules.
     *
     */
    public function getSchedules(): array
    {
        return $this->addSchedule([]);
    }

    /**
     * Schedule the configured WordPress cron event.
     *
     * Existing events are cleared when the configured interval changes.
     * No event is scheduled when cron is disabled or another execution
     * method is configured.
     *
     * @since 1.0.0
     * @return void
     *
     */
    public function schedule(): void
    {
        $this->loadSettings();

        if (!$this->isEnabled() || $this->getMethod() !== 'wordpress') {
            $this->clearScheduledEvents();
            return;
        }
        $currentSchedule = wp_get_schedule(self::HOOK);

        if ($currentSchedule !== false && $currentSchedule !== $this->getInterval()) {
            $this->clearScheduledEvents();
        }

        if (wp_next_scheduled(self::HOOK)) {
            return;
        }

        wp_schedule_event(time(), $this->getInterval(), self::HOOK);
    }

    /**
     * Remove the next scheduled cron event.
     *
     * @since 1.0.0
     * @return void
     *
     */
    public function unschedule(): void
    {
        $timestamp = wp_next_scheduled(self::HOOK);

        if ($timestamp === false) {
            return;
        }

        wp_unschedule_event($timestamp, self::HOOK);
    }

    /**
     * Determine whether there are pending transfers.
     *
     * @since 1.0.0
     * @return bool True when at least one transfer is pending.
     *
     */
    public function hasPendingTransfer()
    {
        return $this->scanFileRepository->findPendingTransfer() !== false;
    }

    /**
     * Get the configured WordPress cron schedule identifier.
     *
     * @since 1.0.0
     * @return string Cron schedule identifier.
     *
     */
    private function getInterval(): string
    {
        return (string)($this->setting_cron['interval'] ?? 'magical_connection_5_minutes');
    }

    /**
     * Get the configured cron execution method.
     *
     * @since 1.0.0
     * @return string Cron execution method.
     *
     */
    private function getMethod(): string
    {
        return (string)($this->setting_cron['method'] ?? 'wordpress');
    }

    /**
     * Get the configured number of files processed per run.
     *
     * @since 1.0.0
     * @return int Maximum files processed by one cron execution.
     *
     */
    private function isEnabled(): bool
    {
        return (bool)($this->setting_cron['enabled'] ?? false);
    }

    /**
     * Determine whether cron execution is enabled.
     *
     * @since 1.0.0
     * @return bool True when cron execution is enabled.
     *
     */
    private function clearScheduledEvents(): void
    {
        wp_clear_scheduled_hook(self::HOOK);
    }

    /**
     * Clear all scheduled events registered for the plugin cron hook.
     *
     * @since 1.0.0
     * @return void
     *
     */
    private function loadSettings()
    {
        $this->setting_cron = mc_setting()->get('cron');
    }
}