<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\AutoTransfer\Repositories;

use MagicalConnection\Modules\AutoTransfer\Models\TransferQueueModel;

/**
 * Provide repository operations for the transfer queue.
 *
 * Acts as an application-level abstraction over the transfer queue model
 * and exposes the operations required by the automatic transfer process.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class TransferQueueRepository
{

    /**
     * Transfer queue model used to access queue data.
     *
     * @since 1.0.0
     */
    private TransferQueueModel $model;

    /**
     * Create a new transfer queue repository instance.
     *
     * @since 1.0.0
     *
     * @param TransferQueueModel $model Transfer queue model instance.
     */
    public function __construct(TransferQueueModel $model)
    {
        $this->model = $model;
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
        return $this->model->findByScanFileId($scanFileId);
    }

    /**
     * Create a new transfer queue record.
     *
     * @since 1.0.0
     *
     * @param array $data Transfer queue data.
     *
     * @return int|false Inserted queue record ID, or false when insertion fails.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Mark a pending queue record as currently transferring.
     *
     * Increments the attempt number before passing it to the model.
     *
     * @since 1.0.0
     *
     * @param int $id       Queue record ID.
     * @param int $attempts Current attempt number.
     *
     * @return bool True when the record was updated successfully.
     */
    public function markTransferring(int $id, int $attempts = 1): bool
    {
        return $this->model->markTransferring($id, ($attempts + 1));
    }

    /**
     * Mark a transferring queue record as successfully transferred.
     *
     * @since 1.0.0
     *
     * @param int $id Queue record ID.
     *
     * @return bool True when the record was updated successfully.
     */
    public function markTransferred(int $id): bool
    {
        return $this->model->markTransferred($id);
    }

    /**
     * Mark a transferring queue record as failed.
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
        return $this->model->markFailed($id, $errorMessage);
    }

    /**
     * Find the next scanned file that has not been added to the queue.
     *
     * @since 1.0.0
     *
     * @return array|null Scanner file record if available, otherwise null.
     */
    public function findNextFileForTransfer(): ?array
    {
        return $this->model->findNextFileForTransfer();
    }

    /**
     * Find the currently active transfer.
     *
     * @since 1.0.0
     *
     * @return array|null Active queue record, otherwise null.
     */
    public function findTransferring(): ?array
    {
        return $this->model->findTransferring();
    }

    /**
     * Find the oldest failed transfer that can be retried.
     *
     * @since 1.0.0
     *
     * @param int $maxAttempts Maximum number of allowed attempts.
     *
     * @return array|null Retryable failed queue record, otherwise null.
     */
    public function findRetryableFailed(int $maxAttempts = 3): ?array
    {
        return $this->model->findRetryableFailed($maxAttempts);
    }

    /**
     * Find the oldest pending transfer.
     *
     * @since 1.0.0
     *
     * @return array|null Pending queue record, otherwise null.
     */
    public function findPending(): ?array
    {
        return $this->model->findPending();
    }
}