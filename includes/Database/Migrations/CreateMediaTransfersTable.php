<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the media transfers database table.
 *
 * Stores the transfer state and remote location of media files
 * associated with remote connections.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateMediaTransfersTable
{
    /**
     * Get the media transfers table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_media_transfers';
    }

    /**
     * Create the media transfers table.
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
            attachment_id BIGINT UNSIGNED NOT NULL,
            connection_id BIGINT UNSIGNED NOT NULL,
            local_path TEXT NOT NULL,
            remote_path TEXT NOT NULL,
            remote_url TEXT NOT NULL,
            file_type VARCHAR(20) NOT NULL DEFAULT 'original',
            media_size VARCHAR(20) NOT NULL DEFAULT 'original',
            status VARCHAR(20) NOT NULL DEFAULT 'transferred',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_attachment_id (attachment_id),
            KEY idx_connection_id (connection_id),
            KEY idx_status (status),
            KEY idx_attachment_connection (
                attachment_id,
                connection_id
            ),
            KEY idx_attachment_media_size (
                attachment_id,
                media_size
            )
        ) {$charset};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}