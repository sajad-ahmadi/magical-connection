<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\AutoTransfer\Models;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateScanFilesTable;
use MagicalConnection\Database\Migrations\CreateTransferQueueTable;

/**
 * Manage transfer queue records.
 *
 * Provides database operations for creating, retrieving, and updating
 * transfer queue records used by the automatic media transfer process.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class TransferQueueModel
{
    /**
     * Queue status indicating that the transfer is waiting to be processed.
     *
     * @since 1.0.0
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Queue status indicating that the transfer is currently being processed.
     *
     * @since 1.0.0
     */
    public const STATUS_TRANSFERRING = 'transferring';

    /**
     * Queue status indicating that the transfer completed successfully.
     *
     * @since 1.0.0
     */
    public const STATUS_TRANSFERRED = 'transferred';

    /**
     * Queue status indicating that the transfer failed.
     *
     * @since 1.0.0
     */
    public const STATUS_FAILED = 'failed';

    /**
     * Database service used to execute queue queries.
     *
     * @since 1.0.0
     */
    private Database $database;

    /**
     * Transfer queue database table name.
     *
     * @since 1.0.0
     */
    private string $table;

    /**
     * Scan files database table name.
     *
     * Used when finding scanned files that have not yet been
     * added to the transfer queue.
     *
     * @since 1.0.0
     */
    private string $table_name_scan_files;

    /**
     * Create a new transfer queue model instance.
     *
     * Resolves the database table names from their migration definitions
     * so the model remains consistent with the application's table schema.
     *
     * @since 1.0.0
     *
     * @param Database                  $database             Database service instance.
     * @param CreateTransferQueueTable  $table                Transfer queue table definition.
     * @param CreateScanFilesTable      $createScanFilesTable Scan files table definition.
     */
    public function __construct(Database $database, CreateTransferQueueTable $table, CreateScanFilesTable $createScanFilesTable)
    {
        $this->table = $table->tableName();
        $this->table_name_scan_files = $createScanFilesTable->tableName();
        $this->database = $database;
    }

    /**
     * Create a new transfer queue record.
     *
     * New records always start in the pending state with zero
     * transfer attempts.
     *
     * @since 1.0.0
     *
     * @param array $data Transfer queue data.
     *
     * @return int|false Inserted queue record ID, or false when insertion fails.
     */
    public function create(array $data)
    {
        $now = current_time('mysql');
        $insertId = $this->database->insert(
            $this->table,
            [
                'scan_file_id'  => (int)$data['scan_file_id'],
                'attachment_id' => (int)$data['attachment_id'],
                'connection_id' => (int)$data['connection_id'],
                'status'        => self::STATUS_PENDING,
                'attempts'      => 0,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        );

        return $insertId;
    }

    /**
     * Find a queue record by its scanner file ID.
     *
     * @since 1.0.0
     *
     * @param int $scanFileId Scanner file ID.
     *
     * @return array|null Queue record if found, otherwise null.
     */
    public function findByScanFileId(int $scanFileId): ?array
    {
        $sql = $this->database->prepare(
            " SELECT * FROM %i WHERE scan_file_id = %d",
            $this->table,
            $scanFileId
        );
        $result = $this->database->getRow($sql);
        return $result ?: null;
    }

    /**
     * Find the oldest pending transfer.
     *
     * Pending records are ordered by their queue ID so older
     * records are processed first.
     *
     * @since 1.0.0
     *
     * @return array|null Pending queue record, otherwise null.
     */
    public function findPending(): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE status = %s ORDER BY id ASC LIMIT 1",
            $this->table,
            self::STATUS_PENDING
        );

        $result = $this->database->getRow($sql);
        return $result ?: null;
    }

    /**
     * Find the currently active transfer.
     *
     * Returns the oldest queue record currently marked as transferring.
     *
     * @since 1.0.0
     *
     * @return array|null Active queue record, otherwise null.
     */
    public function findTransferring(): ?array
    {
        $sql = $this->database->prepare("SELECT * FROM %i WHERE status = %s ORDER BY id ASC LIMIT 1",
            $this->table,
            self::STATUS_TRANSFERRING
        );

        $result = $this->database->getRow($sql);
        return $result ?: null;
    }

    /**
     * Find the oldest failed transfer that can be retried.
     *
     * A failed record is retryable while its attempt count is
     * lower than the configured maximum.
     *
     * @since 1.0.0
     *
     * @param int $maxAttempts Maximum number of allowed attempts.
     *
     * @return array|null Retryable failed queue record, otherwise null.
     */
    public function findRetryableFailed(int $maxAttempts = 3): ?array
    {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE status = %s AND attempts < %d ORDER BY id ASC LIMIT 1",
            $this->table,
            self::STATUS_FAILED,
            $maxAttempts
        );

        $result = $this->database->getRow($sql);
        return $result ?: null;
    }

    /**
     * Mark a pending queue record as currently transferring.
     *
     * Stores the current attempt number and locks the record
     * so it can be treated as an active transfer.
     *
     * @since 1.0.0
     *
     * @param int $id       Queue record ID.
     * @param int $attempts Number of transfer attempts.
     *
     * @return bool True when the record was updated successfully.
     */
    public function markTransferring(int $id, int $attempts = 1): bool
    {
        $now = current_time('mysql');
        $result = $this->database->update(
            $this->table,
            [
                'status'     => self::STATUS_TRANSFERRING,
                'attempts'   => $attempts,
                'locked_at'  => $now,
                'started_at' => $now,
                'updated_at' => $now,

            ], [
                'id'     => $id,
                'status' => self::STATUS_PENDING,
            ]
        );
        return $result;
    }

    /**
     * Mark a transferring queue record as successfully transferred.
     *
     * Clears the previous error and lock information and stores
     * the completion timestamp.
     *
     * @since 1.0.0
     *
     * @param int $id Queue record ID.
     *
     * @return bool True when the record was updated successfully.
     */
    public function markTransferred(int $id): bool
    {
        $now = current_time('mysql');
        $result = $this->database->update(
            $this->table,
            [
                'status'         => self::STATUS_TRANSFERRED,
                'error_message'  => NULL,
                'locked_at'      => NULL,
                'transferred_at' => $now,
                'updated_at'     => $now,

            ], [
                'id'     => $id,
                'status' => self::STATUS_TRANSFERRING,
            ]
        );
        return $result === 1;
    }

    /**
     * Mark a transferring queue record as failed.
     *
     * Stores the failure message and releases the active transfer lock
     * so the record can later be considered for retry.
     *
     * @since 1.0.0
     *
     * @param int    $id           Queue record ID.
     * @param string $errorMessage Error message describing the failure.
     *
     * @return bool True when the record was updated successfully.
     */
    public function markFailed(int $id, string $errorMessage): bool
    {
        $now = current_time('mysql');
        $result = $this->database->update(
            $this->table,
            [
                'status'        => self::STATUS_FAILED,
                'error_message' => $errorMessage,
                'locked_at'     => NULL,
                'updated_at'    => $now,

            ], [
                'id'     => $id,
                'status' => self::STATUS_TRANSFERRING,
            ]
        );
        return $result === 1;
    }

    /**
     * Find the next scanned file that has not been added to the transfer queue.
     *
     * Only scanned files that are marked as not transferred and have
     * no corresponding queue record are returned.
     *
     * @since 1.0.0
     *
     * @return array|null Scanner file record if available, otherwise null.
     */
    public function findNextFileForTransfer(): ?array
    {
        $sql = $this->database->prepare("
        SELECT sf.* FROM %i sf
        LEFT JOIN %i tq
        ON tq.scan_file_id = sf.id
        WHERE sf.transferred = 0
        AND tq.id IS NULL
        ORDER BY sf.id ASC LIMIT 1",
            $this->table_name_scan_files,
            $this->table
        );

        return $this->database->getRow($sql);
    }
}