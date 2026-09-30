<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Setting\Model;


use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateSettingsTable;

/**
 * Manage plugin setting records in the database.
 *
 * Provides database operations for reading, creating, updating, and retrieving
 * all persisted plugin settings.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class SettingModel
{

    private Database $database;
    /**
     * Settings table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Create the settings model.
     *
     * @param Database            $database Database abstraction.
     * @param CreateSettingsTable $table    Settings table definition.
     */
    public function __construct(Database $database, CreateSettingsTable $table)
    {
        $this->table = $table->tableName();
        $this->database = $database;
    }

    /**
     * Find a setting by its key.
     *
     * @param string $key Setting key.
     *
     * @return array|null Setting record or null when not found.
     */
    public function find(string $key): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE `setting_key`=%s",
            $this->table,
            $key
        );
        return $this->database->getRow($sql);
    }

    /**
     * Retrieve all persisted settings.
     *
     * @return array Setting records.
     */
    public function all(): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM WHERE",
            $this->table
        );
        return $this->database->getResults($sql);
    }

    /**
     * Insert a new setting.
     *
     * @param string $key   Setting key.
     * @param string $value Serialized or scalar setting value.
     *
     * @return bool True when the setting was inserted successfully.
     */
    public function insert(string $key, string $value): bool
    {
        $insertId = $this->database->insert(
            $this->table,
            [
                'setting_key'   => $key,
                'setting_value' => $value
            ]
        );
        if ($insertId > 0) {
            return true;
        }
        return false;
    }

    /**
     * Update an existing setting.
     *
     * @param string $key   Setting key.
     * @param string $value Serialized or scalar setting value.
     *
     * @return bool True when the setting was updated successfully.
     */
    public function update(string $key, string $value): bool
    {
        return $this->database->update(
            $this->table,
            [
                'setting_value' => $value
            ],
            [
                'setting_key' => $key
            ]
        );
    }

}