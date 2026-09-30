<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the media references database table.
 *
 * Stores the relationship between content items and their media attachments.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateMediaReferencesTable
{
    /**
     * Get the media references table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_media_references';
    }

    /**
     * Create the media references table.
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
            content_id BIGINT UNSIGNED NOT NULL,
            attachment_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_content_attachment (
                content_id,
                attachment_id
            ),
            KEY idx_attachment_id (
                attachment_id
            ),
            KEY idx_content_id (
                content_id
            )
        ) {$charset};
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}