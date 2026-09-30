<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\AutoTransfer\Services;

use MagicalConnection\Modules\AutoTransfer\Models\TransferQueueModel;
use MagicalConnection\Modules\AutoTransfer\Repositories\TransferQueueRepository;
use MagicalConnection\Modules\Media\Service\MediaTransferService;
use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Modules\Setting\Repositories\SettingRepository;
use Throwable;

/**
 * Manage automatic media transfers from the scan queue.
 *
 * Coordinates scanned files, transfer queue records, connection settings,
 * transfer execution, retry handling, and stale transfer locks.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
final class AutoTransferService
{
    /**
     * Media transfer service used to execute file transfers.
     *
     * @since 1.0.0
     */
    private MediaTransferService $mediaTransferService;

    /**
     * Repository used to manage transfer queue records.
     *
     * @since 1.0.0
     */
    private TransferQueueRepository $transferQueueRepository;

    /**
     * Repository used to retrieve automatic transfer settings.
     *
     * @since 1.0.0
     */
    private SettingRepository $settingRepository;

    /**
     * Repository used to update scan file transfer state.
     *
     * @since 1.0.0
     */
    private ScanFileRepository $scanFileRepository;

    /**
     * Maximum number of transfer attempts allowed for a queue item.
     *
     * @since 1.0.0
     */
    private const MAX_ATTEMPTS = 3;

    /**
     * Maximum time a transfer may remain locked before it is considered stale.
     *
     * @since 1.0.0
     */
    private const LOCK_TIMEOUT = 30 * MINUTE_IN_SECONDS;

    /**
     * Create a new automatic transfer service.
     *
     * @since 1.0.0
     *
     * @param MediaTransferService    $mediaTransferService    Media transfer service.
     * @param SettingRepository       $settingRepository       Settings repository.
     * @param TransferQueueRepository $transferQueueRepository Transfer queue repository.
     * @param ScanFileRepository      $scanFileRepository      Scan file repository.
     */
    public function __construct(
        MediaTransferService    $mediaTransferService,
        SettingRepository       $settingRepository,
        TransferQueueRepository $transferQueueRepository,
        ScanFileRepository      $scanFileRepository
    )
    {
        $this->mediaTransferService = $mediaTransferService;
        $this->settingRepository = $settingRepository;
        $this->transferQueueRepository = $transferQueueRepository;
        $this->scanFileRepository = $scanFileRepository;
    }

    /**
     * Run one automatic transfer operation.
     *
     * Stops immediately when automatic transfer is disabled,
     * no transferable queue item exists, or a queue item cannot
     * be prepared for processing.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function run(): void
    {
        if (!$this->isActiveAutoTransfer()) {
            return;
        }

        $queue = $this->getNextTransfer();

        if ($queue === null) {
            return;
        }

        $queue = $this->getOrCreateQueue($queue);

        if ($queue === null) {
            return;
        }

        $this->transfer($queue);
    }

    /**
     * Execute a transfer queue item.
     *
     * The queue item is locked before the transfer starts. On success,
     * the queue and corresponding scan file are marked as transferred.
     * Transfer failures are stored on the queue for retry handling.
     *
     * @since 1.0.0
     *
     * @param array $queue Transfer queue record.
     *
     * @return void
     */
    private function transfer(array $queue): void
    {
        $queueId = (int)$queue['id'];
        if (!$this->transferQueueRepository->markTransferring($queueId, (int)$queue['attempts'])) {
            return;
        }

        try {
            $this->mediaTransferService->transfer((int)$queue['attachment_id'], (int)$queue['connection_id']);
            $this->transferQueueRepository->markTransferred($queueId);
            $this->scanFileRepository->markAsTransferredById((int)$queue['scan_file_id']);
        } catch (Throwable $exception) {
            $this->transferQueueRepository->markFailed($queueId, $exception->getMessage());
        }
    }

    /**
     * Find the next scanned file that can be added to the queue.
     *
     * @since 1.0.0
     *
     * @return array|null Scanner file record if available.
     */
    private function getNextFile(): ?array
    {
        return $this->transferQueueRepository->findNextFileForTransfer();
    }

    /**
     * Resolve an existing queue record or create one for a scanned file.
     *
     * A scanned file is associated with the configured automatic transfer
     * connection when a queue record does not already exist.
     *
     * @since 1.0.0
     *
     * @param array $file Scanned file record.
     *
     * @return array|null Queue record if available.
     */
    private function getOrCreateQueue(array $file): ?array
    {
        $queue = $this->transferQueueRepository->findByScanFileId((int)$file['id']);
        if ($queue !== null) {
            return $queue;
        }

        if ($queue !== null && $queue['status'] === TransferQueueModel::STATUS_TRANSFERRED) {
            return null;
        }

        $connectionId = $this->getConnectionId();
        if ($connectionId === null) {
            return null;
        }

        $queueId = $this->transferQueueRepository->create([
            'scan_file_id'  => (int)$file['id'],
            'attachment_id' => (int)$file['attachment_id'],
            'connection_id' => $connectionId,
        ]);

        if (!$queueId) {
            return null;
        }
        return $this->transferQueueRepository->findByScanFileId($queueId);
    }

    /**
     * Find the next queue item that should be processed.
     *
     * Active transfers are checked first so stale locks can be released.
     * Pending transfers are processed before retryable failed transfers.
     * When no queue record exists, the next eligible scanned file is returned.
     *
     * @since 1.0.0
     *
     * @return array|null Queue or scanned file record to process.
     */
    private function getNextTransfer(): ?array
    {
        $transferring = $this->transferQueueRepository->findTransferring();

        if ($transferring !== null) {
            if (!$this->isLockExpired($transferring)) {
                return null;
            }
            $this->transferQueueRepository->markFailed(
                (int)$transferring['id'],
                'Transfer lock expired.'
            );
        }

        $pending = $this->transferQueueRepository->findPending();

        if ($pending !== null) {
            return $pending;
        }

        $failed = $this->transferQueueRepository->findRetryableFailed(self::MAX_ATTEMPTS);

        if ($failed !== null) {
            return $failed;
        }

        return $this->getNextFile();
    }

    /**
     * Determine whether a transfer lock has expired.
     *
     * Missing or invalid lock timestamps are treated as expired so
     * a stale queue item cannot remain permanently locked.
     *
     * @since 1.0.0
     *
     * @param array $queue Transfer queue record.
     *
     * @return bool True when the lock has expired.
     */
    private function isLockExpired(array $queue): bool
    {
        if (empty($queue['locked_at'])) {
            return true;
        }

        $lockedAt = strtotime($queue['locked_at']);

        if ($lockedAt === false) {
            return true;
        }

        return (time() - $lockedAt) >= self::LOCK_TIMEOUT;
    }

    /**
     * Get the connection configured for automatic transfers.
     *
     * @since 1.0.0
     *
     * @return int|null Configured connection ID, or null when unavailable.
     */
    private function getConnectionId(): ?int
    {
        $connectionId = $this->settingRepository->get('auto_transfer.connection_id');
        if (!$connectionId) {
            return null;
        }
        return (int)$connectionId;
    }

    /**
     * Determine whether automatic transfer is enabled.
     *
     * @since 1.0.0
     *
     * @return bool True when automatic transfer is enabled.
     */
    private function isActiveAutoTransfer()
    {
        return $this->settingRepository->get('auto_transfer.enabled') === true;
    }
}