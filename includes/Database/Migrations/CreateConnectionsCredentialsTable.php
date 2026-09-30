<?php

declare(strict_types=1);

namespace MagicalConnection\Database\Migrations;


/**
 * Create the connection credentials database table.
 *
 * Stores authentication credentials associated with each connection.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class CreateConnectionsCredentialsTable
{
    /**
     * Get the connection credentials table name.
     *
     * @since 1.0.0
     *
     * @return string Fully qualified table name.
     */
    public function tableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'magical_connection_credentials';
    }

    /**
     * Create the connection credentials table.
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
            connection_id bigint(20) unsigned NOT NULL,
            username varchar(191) NULL,
            password longtext NULL,
            private_key longtext NULL,
            passphrase longtext NULL,
            token longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY connection_id (connection_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);

    }
}