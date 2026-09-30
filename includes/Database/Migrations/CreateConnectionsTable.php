<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;

/**
 * Create the connections database table.
 *
 * Stores the configuration and connection settings for remote hosts.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateConnectionsTable
{
    /**
     * Get the connections table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'magical_connections';
    }

    /**
     * Create the connections table.
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
            `name` varchar(191) NOT NULL,
            protocol varchar(20) NOT NULL,
            host varchar(255) NOT NULL,
            `domain` varchar(500) NOT NULL,
            port smallint(5) unsigned NOT NULL,
            base_path varchar(500) NULL,
            timeout int(10) unsigned NOT NULL DEFAULT 300,
            status tinyint(1) NOT NULL DEFAULT 1,
            passive_mod tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY protocol (protocol),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}