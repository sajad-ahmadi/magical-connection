<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the scan files database table.
 *
 * Stores files discovered during media scans and their transfer state.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateScanFilesTable
{
    /**
     * Get the scan files table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_scan_files';
    }

    /**
     * Create the scan files table.
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
            attachment_id BIGINT UNSIGNED NOT NULL,
            scan_find_id BIGINT UNSIGNED NOT NULL,
            scan_updated_id BIGINT UNSIGNED NOT NULL,
            filename VARCHAR(255) NOT NULL,
            relative_path TEXT NOT NULL,
            extension VARCHAR(20) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_type VARCHAR(20) NOT NULL,
            size BIGINT UNSIGNED NOT NULL DEFAULT 0,
            transferred TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY attachment_id (attachment_id),
            KEY file_type (file_type),
            KEY extension (extension),
            KEY transferred (transferred)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}