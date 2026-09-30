<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the scans database table.
 *
 * Stores scan sessions, their progress, and transfer statistics.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateScansTable
{
    /**
     * Get the scans table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_scans';
    }

    /**
     * Create the scans table.
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
        CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid CHAR(36) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            total_files BIGINT UNSIGNED NOT NULL DEFAULT 0,
            transferred_files BIGINT UNSIGNED NOT NULL DEFAULT 0,
            not_transferred_files BIGINT UNSIGNED NOT NULL DEFAULT 0,
            current_page INT UNSIGNED NOT NULL DEFAULT 0,
            started_at DATETIME NULL,
            completed_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}