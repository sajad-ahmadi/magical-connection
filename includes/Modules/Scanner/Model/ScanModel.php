<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Model;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateScansTable;

/**
 * Manage scanner records.
 *
 * Provides database operations for scanner sessions and their
 * lifecycle records.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ScanModel
{

    private Database $database;

    /**
     * Scans table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Create the scan model.
     *
     * @param Database         $database Database abstraction.
     * @param CreateScansTable $table    Scans table definition.
     *
     * @since 1.0.0
     */
    public function __construct(Database $database, CreateScansTable $table)
    {
        $this->table = $table->tableName();
        $this->database = $database;
    }

    /**
     * Insert a scan record.
     *
     * @param array $data Scan data.
     *
     * @return int|false Inserted record ID or false on failure.
     *
     * @since 1.0.0
     */
    public function insert(array $data)
    {
        return $this->database->insert(
            $this->table,
            $data
        );
    }

    /**
     * Update a scan record by ID.
     *
     * @param int                 $id   Scan record ID.
     * @param array $data Fields to update.
     *
     * @return bool Whether the update succeeded.
     *
     * @since 1.0.0
     */
    public function updateById(int $id, array $data): bool
    {
        return $this->database->update(
            $this->table,
            $data,
            [
                'id' => $id,
            ]
        );
    }

    /**
     * Find a scan by its database ID.
     *
     * @param int $id Scan record ID.
     *
     * @return array|null Scan record or null when not found.
     *
     * @since 1.0.0
     */
    public function findById(int $id): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $this->table,
            $id
        );
        return $this->database->getRow($sql);
    }

    /**
     * Find a scan by its UUID.
     *
     * @param string $uuid Scan UUID.
     *
     * @return array|null Scan record or null when not found.
     *
     * @since 1.0.0
     */
    public function findByUuid(string $uuid): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE uuid = %d",
            $this->table,
            $uuid
        );
        return $this->database->getRow($sql);
    }

    /**
     * Find the most recently created scan.
     *
     * @return array|null Latest scan record or null when none exists.
     *
     * @since 1.0.0
     */
    public function findLatest(): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i ORDER BY id DESC LIMIT 1",
            $this->table,
        );
        return $this->database->getRow($sql);
    }

    /**
     * Delete a scan by ID.
     *
     * @param int $scanId Scan record ID.
     *
     * @return bool Whether the record was deleted.
     *
     * @since 1.0.0
     */
    public function delete(int $scanId): bool
    {
        return $this->database->delete($this->table, ['id' => $scanId]);
    }
}