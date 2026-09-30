<?php
declare(strict_types=1);

namespace MagicalConnection\Core;


use MagicalConnection\Database\Migrations\CreateConnectionsCredentialsTable;
use MagicalConnection\Database\Migrations\CreateConnectionsTable;
use MagicalConnection\Database\Migrations\CreateMediaReferencesTable;
use MagicalConnection\Database\Migrations\CreateMediaTransfersTable;
use MagicalConnection\Database\Migrations\CreateScanFilesTable;
use MagicalConnection\Database\Migrations\CreateScansTable;
use MagicalConnection\Database\Migrations\CreateSettingsTable;
use MagicalConnection\Database\Migrations\CreateTransferQueueTable;
use MagicalConnection\Modules\Setting\Services\SettingManager;

/**
 * Handle plugin activation tasks.
 *
 * Creates the database tables required by Magical Connection
 * and initializes the plugin settings.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Activation
{
    /**
     * Run the plugin activation tasks.
     *
     * Database migrations are executed before the initial settings
     * are loaded into the application.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate(): void
    {
        (new CreateSettingsTable())->up();
        (new CreateConnectionsTable())->up();
        (new CreateConnectionsCredentialsTable())->up();
        (new CreateMediaTransfersTable())->up();
        (new CreateMediaReferencesTable())->up();
        (new CreateScansTable())->up();
        (new CreateScanFilesTable())->up();
        (new CreateTransferQueueTable())->up();

        mc_app()->make(SettingManager::class)->boot();
    }
}