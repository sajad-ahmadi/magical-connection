<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the settings database table.
 *
 * Stores application settings as key-value pairs.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateSettingsTable
{
    /**
     * Get the settings table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_settings';
    }

    /**
     * Create the settings table.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function up(): void
    {
        global $wpdb;

        $table = $this->tableName();
        $charset = $wpdb->get_charset_collate();

        $sql = "
        CREATE TABLE IF NOT EXISTS {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            setting_key varchar(191) NOT NULL,
            setting_value longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) {$charset};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}