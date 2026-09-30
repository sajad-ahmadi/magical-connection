<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Setting;

/**
 * Provide the default plugin settings.
 *
 * Defines the initial configuration used when the plugin settings are
 * initialized or when new setting keys are introduced.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class DefaultSettings
{

    /**
     * Get all default plugin settings.
     *
     * The returned array is grouped by setting namespace. Individual values
     * can be accessed through SettingManager using dot notation such as
     * `cron.enabled` or `auto_transfer.connection_id`.
     *
     * @return array Default grouped settings.
     */
    public static function all(): array
    {
        return [
            'auto_transfer' => [
                'enabled'       => false,
                'connection_id' => 0,
            ],
            'cron'          => [
                'enabled'       => false,
                'interval'      => 'magical_connection_5_minutes',
                'method'        => 'wordpress',
                'files_per_run' => 10,
                'max_retries'   => 3,
            ],
            'permissions'   => [
                'allowed_roles' => [],
            ],
        ];
    }
}