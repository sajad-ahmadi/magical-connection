<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Model;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateScanFilesTable;

/**
 * Manage scanner file records.
 *
 * Provides database operations for scanned attachment files,
 * transfer state, and scanner statistics.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ScanFileModel
{
    private Database $database;


    /**
     * Scan files table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Create the scan file model.
     *
     * @param Database            $database Database abstraction.
     * @param CreateScanFilesTable $table   Scan files table definition.
     *
     * @since 1.0.0
     */
    public function __construct(Database $database, CreateScanFilesTable $table)
    {
        $this->table = $table->tableName();
        $this->database = $database;
    }

    /**
     * Insert a scan file record.
     *
     * @param array $data Scan file data.
     *
     * @return int|false Inserted record ID or false on failure.
     *
     * @since 1.0.0
     */
    public function insert(array $data)
    {
        return $this->database->insert($this->table, $data);
    }

    /**
     * Update a scan file by attachment ID.
     *
     * @param int                 $attachmentId Attachment ID.
     * @param array $data         Fields to update.
     *
     * @return bool Whether the update succeeded.
     *
     * @since 1.0.0
     */
    public function updateByAttachmentId(int $attachmentId, array $data): bool
    {
        return $this->database->update(
            $this->table,
            $data,
            [
                'attachment_id' => $attachmentId,
            ]
        );
    }

    /**
     * Find a scan file by attachment ID.
     *
     * @param int $attachmentId Attachment ID.
     *
     * @return array|null Scan file record or null when not found.
     *
     * @since 1.0.0
     */
    public function findByAttachmentId(int $attachmentId): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE attachment_id = %d LIMIT 1",
            $this->table,
            $attachmentId
        );
        return $this->database->getRow($sql);
    }

    /**
     * Retrieve all scan file records.
     *
     * @return array Scan file records ordered by ID.
     *
     * @since 1.0.0
     */
    public function findAll(): array
    {
        $sql = $this->database->prepare("SELECT * FROM %i ORDER BY id ASC", $this->table);
        return $this->database->getResults($sql);
    }

    /**
     * Get aggregate scan file transfer statistics.
     *
     * @return array Total, transferred, and non-transferred counts.
     *
     * @since 1.0.0
     */
    public function getStatistics(): array
    {
        $sql = $this->database->prepare("SELECT COUNT(*) AS total, SUM(transferred = 1) AS transferred, SUM(transferred = 0) AS not_transferred FROM %i", $this->table);
        return $this->database->getRow($sql);
    }

    /**
     * Get transfer statistics grouped by file type.
     *
     * Archive files are grouped into the `other` category.
     *
     * @return array Statistics grouped by file type.
     *
     * @since 1.0.0
     */
    public function getStatisticsByType(): array
    {
        $sql = $this->database->prepare(
            "SELECT
            CASE
                WHEN file_type = 'archive' THEN 'other'
                ELSE file_type
            END AS file_type,
            COUNT(*) AS total,
            SUM(transferred = 1) AS transferred,
            SUM(transferred = 0) AS not_transferred
        FROM %i
        GROUP BY
            CASE
                WHEN file_type = 'archive' THEN 'other'
                ELSE file_type
            END
        ORDER BY total DESC",
            $this->table
        );
        return $this->database->getResults($sql);
    }

    /**
     * Get transfer statistics grouped by file extension.
     *
     * Includes file counts, transfer distribution, percentages,
     * and total file sizes for local and remote states.
     *
     * @return array Statistics grouped by extension.
     *
     * @since 1.0.0
     */
    public function getStatisticsByExtension(): array
    {
        $sql = $this->database->prepare(
            "SELECT
        *,
        COUNT(*) AS total_files,

        SUM(transferred = 0) AS in_wordpress,
        SUM(transferred = 1) AS in_remote,

        ROUND(
            SUM(transferred = 0) * 100.0 / COUNT(*),
            1
        ) AS wordpress_percentage,

        ROUND(
            SUM(transferred = 1) * 100.0 / COUNT(*),
            1
        ) AS remote_percentage,

        COALESCE(
            SUM(CASE WHEN transferred = 0 THEN size ELSE 0 END),
            0
        ) AS wordpress_size,

        COALESCE(
            SUM(CASE WHEN transferred = 1 THEN size ELSE 0 END),
            0
        ) AS remote_size

    FROM %i

    GROUP BY extension
    ORDER BY total_files DESC",
            $this->table
        );
        return $this->database->getResults($sql);
    }

    /**
     * Mark a scan file as transferred by record ID.
     *
     * @param int $id Scan file record ID.
     *
     * @return bool Whether the record was updated.
     *
     * @since 1.0.0
     */
    public function markAsTransferredById(int $id): bool
    {
        $result = $this->database->update(
            $this->table,
            [
                'transferred' => 1,
            ],
            [
                'id' => $id,
            ]
        );
        return $result === 1;
    }

    /**
     * Update transfer state by attachment ID.
     *
     * @param int  $id          Attachment ID.
     * @param bool $transferred Whether the attachment is transferred.
     *
     * @return bool Whether the update succeeded.
     *
     * @since 1.0.0
     */
    public function markAsTransferredByAttachmentId(int $id, bool $transferred): bool
    {
        $result = $this->database->update(
            $this->table,
            [
                'transferred' => $transferred ? 1 : 0,
            ],
            [
                'attachment_id' => $id,
            ]
        );
        return $result;
    }

    /**
     * Find the next scan file pending transfer.
     *
     * Returns the oldest untransferred scan file record.
     *
     * @return array|null Scan file record or null when none exists.
     *
     * @since 1.0.0
     */
    public function findPendingTransferId()
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE transferred = 0 ORDER BY id ASC LIMIT 1",
            $this->table
        );
        return $this->database->getRow($sql);
    }
}