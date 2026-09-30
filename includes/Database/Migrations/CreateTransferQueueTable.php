<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the transfer queue database table.
 *
 * Stores queued media transfers and tracks their execution state,
 * retry attempts, locking, and transfer timestamps.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateTransferQueueTable
{
    /**
     * Get the transfer queue table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_transfer_queue';
    }

    /**
     * Create the transfer queue table.
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
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            scan_file_id BIGINT UNSIGNED NOT NULL,
            attachment_id BIGINT UNSIGNED NOT NULL,
            connection_id BIGINT UNSIGNED NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            attempts INT UNSIGNED NOT NULL DEFAULT 0,
            error_message TEXT NULL,
            locked_at DATETIME NULL,
            started_at DATETIME NULL,
            transferred_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_scan_file_id (scan_file_id),
            KEY idx_attachment_id (attachment_id),
            KEY idx_connection_id (connection_id),
            KEY idx_status (status),
            KEY idx_status_locked_at (status, locked_at)
        ) {$charset};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}