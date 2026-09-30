<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Services;

use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Modules\Scanner\Repositories\ScanRepository;
use MagicalConnection\Support\MagicalConnectionException;
use Throwable;
use WP_Query;

/**
 * Scan WordPress media attachments and track their transfer state.
 *
 * Supports both paginated real-time scanning and full background scanning.
 * Scan progress and per-attachment results are persisted through the scanner
 * repositories so interrupted scans can be resumed.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class MediaScannerService
{
    private TransferStatusResolver $transferStatusResolver;
    private ScanRepository $scanRepository;
    private ScanFileRepository $scanFileRepository;

    /**
     * Determine whether the current scan should return files incrementally.
     *
     * @var bool
     */
    private bool $isRealTimeScan = false;

    /**
     * Create the media scanner service.
     *
     * @param ScanRepository $scanRepository                 Scan repository.
     * @param ScanFileRepository $scanFileRepository         Scan file repository.
     * @param TransferStatusResolver $transferStatusResolver Transfer status resolver.
     */
    public function __construct(ScanRepository $scanRepository, ScanFileRepository $scanFileRepository, TransferStatusResolver $transferStatusResolver)
    {
        $this->transferStatusResolver = $transferStatusResolver;
        $this->scanRepository = $scanRepository;
        $this->scanFileRepository = $scanFileRepository;
    }

    /**
     * Continue an existing scan from its persisted page.
     *
     * Completed scans are not processed again.
     *
     * @param string $scanUuid Scan UUID.
     * @param int $perPage     Number of attachments to process per page.
     *
     * @return array Scan progress and results.
     */
    public function continueScan(string $scanUuid, int $perPage = 100): array
    {
        $scan = $this->scanRepository->findByUuid($scanUuid);

        if (!$scan) {
            throw new MagicalConnectionException(
                __('Scan not found.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'scanner.scan.not_found'
            );
        }

        if ($scan['status'] === 'completed') {
            return [
                'scan_id'  => $scanUuid,
                'status'   => 'completed',
                'has_more' => false,
            ];
        }

        $perPage = max(1, min($perPage, 500));
        return $this->scan($scanUuid, (int)$scan['current_page'], $perPage);
    }

    /**
     * Process a scan page or the complete attachment collection.
     *
     * A missing scan record is created automatically. Existing scan records
     * are resumed from their persisted state.
     *
     * @param string $scanUuid Scan UUID.
     * @param int $page        Page number to process.
     * @param int $perPage     Number of attachments per page.
     *
     * @return array Scan progress and results.
     */
    public function scan(string $scanUUId, int $page = 1, int $perPage = 100): array
    {
        $scanId = 0;
        $page = max(1, $page);
        $perPage = max(1, min($perPage, 500));

        try {

            $scan = $this->scanRepository->findByUuid($scanUUId);
            if ($scan) {
                $scanId = (int)$scan['id'];
                $startedAt = $scan['started_at'];
            } else {
                $startedAt = current_time('mysql');
                $scanId = $this->scanRepository->create([
                    'uuid'                  => $scanUUId,
                    'status'                => 'running',
                    'total_files'           => 0,
                    'transferred_files'     => 0,
                    'not_transferred_files' => 0,
                    'current_page'          => $page,
                    'started_at'            => $startedAt,
                    'completed_at'          => null,
                    'created_at'            => $startedAt,
                    'updated_at'            => $startedAt,
                ]);
            }

            $total = $this->getTotalAttachments();

            $attachments = $this->getAllAttachments();

            $files = [];
            $types = [];

            $result = [
                'scan_id'         => $scanUUId,
                'page'            => $page,
                'total'           => $total,
                'total_pages'     => $total > 0 ? (int)ceil($total / $perPage) : 0,
                'batch_count'     => 0,
                'transferred'     => 0,
                'not_transferred' => 0,
                'has_more'        => ($page * $perPage) < $total,
                'started_at'      => $startedAt,
                'completed_at'    => null,
                'types'           => $types,
                'files'           => $files,
            ];

            foreach ($attachments as $attachmentId) {
                $file = $this->inspectAttachment((int)$attachmentId);
                if ($file === null) {
                    continue;
                }

                $isTransferred = $this->transferStatusResolver->isTransferred($attachmentId);
                $file['transferred'] = $isTransferred;
                if ($this->isRealTimeScan) {
                    $result['file'] = $file;
                } else {
                    $result['files'][] = $file;
                }
                $this->saveFile($file, $scanId);

                if ($isTransferred) {
                    $result['transferred']++;
                } else {
                    $result['not_transferred']++;
                }

                $this->addTypeStatistics($result['types'], $file['extension'], $isTransferred);
                $result['batch_count']++;

                mc_stream()->log('Uploaded file', [
                    'event_method' => 'scan',
                    'status'       => 'scanning',
                    'data'         => $result,
                ]);
            }

            if (!$result['has_more']) {
                $completedAt = current_time('mysql');
                $statistics = $this->getScanStatistics();

                $this->updateScanStatistics($scanId, $statistics['summary'], $completedAt);

                $result['transferred'] = $statistics['summary']['transferred'];
                $result['not_transferred'] = $statistics['summary']['not_transferred'];
                $result['types'] = $statistics['types'];
            } else {
                $this->scanRepository->update($scanId, ['current_page' => $page + 1]);
            }
            mc_stream()->log('Uploaded file', [
                'event_method' => 'scan',
                'status'       => 'complete',
                'data'         => $result,
            ]);
            return $result;

        } catch (Throwable $exception) {
            if ($scanId != 0) {
                $this->scanFailed($scanId);
            }

            if ($exception instanceof MagicalConnectionException) {
                throw $exception;
            }
            return [];
        }
    }

    /**
     * Delete a persisted scan.
     *
     * @param int $id Scan ID.
     *
     * @return bool True when the scan was deleted.
     */
    public function delete(int $id): bool
    {
        return $this->scanRepository->delete($id);
    }

    /**
     * Enable or disable real-time scan mode.
     *
     * Real-time mode processes one page at a time and exposes the current
     * attachment through the scan result.
     *
     * @param bool $active Whether real-time mode should be active.
     *
     * @return void
     */
    public function realActiveScan(bool $active = false): void
    {
        $this->isRealTimeScan = $active;
    }

    /**
     * Inspect and persist a single attachment for later scanning/transfer.
     *
     * This method is intended for newly added media that needs to enter the
     * scanner state without running a complete scan.
     *
     * @param int $attachmentId Attachment ID.
     *
     * @return void
     */
    public function addFileToScan(int $attachment_id): void
    {
        $file = $this->inspectAttachment($attachment_id);
        if ($file === null) {
            return;
        }

        $isTransferred = $this->transferStatusResolver->isTransferred($attachment_id);
        $file['transferred'] = $isTransferred;

        $this->scanFileRepository->createOrUpdate([
            'scan_find_id'    => 0,
            'scan_updated_id' => 0,
            'attachment_id'   => $file['attachment_id'],
            'filename'        => $file['filename'],
            'relative_path'   => $file['relative_path'],
            'extension'       => $file['extension'],
            'mime_type'       => $file['mime_type'],
            'file_type'       => $this->resolveFileType($file['mime_type']),
            'size'            => $file['size'],
            'transferred'     => $file['transferred'] ? 1 : 0,
        ]);
    }

    /**
     * Persist the inspected attachment state for a scan.
     *
     * @param array $file Inspected attachment data.
     * @param int $scanId               Scan ID.
     *
     * @return void
     */
    private function saveFile(array $file, int $scanId): void
    {
        $this->scanFileRepository->createOrUpdate([
            'scan_find_id'    => $scanId,
            'scan_updated_id' => $scanId,
            'attachment_id'   => $file['attachment_id'],
            'filename'        => $file['filename'],
            'relative_path'   => $file['relative_path'],
            'extension'       => $file['extension'],
            'mime_type'       => $file['mime_type'],
            'file_type'       => $this->resolveFileType($file['mime_type']),
            'size'            => $file['size'],
            'transferred'     => $file['transferred'] ? 1 : 0,
        ]);
    }

    /**
     * Store final statistics and completion state for a scan.
     *
     * @param int $scanId                     Scan ID.
     * @param array $statistics Scan summary statistics.
     * @param string $completedAt             Completion timestamp.
     *
     * @return void
     */
    private function updateScanStatistics(int $scanId, array $statistics, string $completedAt): void
    {
        $this->scanRepository->update(
            $scanId,
            [
                'status'                => 'completed',
                'total_files'           => $statistics['total'],
                'transferred_files'     => $statistics['transferred'],
                'not_transferred_files' => $statistics['not_transferred'],
                'completed_at'          => $completedAt,
                'updated_at'            => $completedAt,
            ]
        );
    }

    /**
     * Mark a scan as failed after an unrecoverable processing error.
     *
     * @param int $scanId Scan ID.
     *
     * @return void
     */
    private function scanFailed(int $scanId): void
    {
        $failedAt = current_time('mysql');
        $this->scanRepository->update(
            $scanId,
            [
                'status'       => 'failed',
                'completed_at' => $failedAt,
                'updated_at'   => $failedAt,
            ]
        );
    }

    /**
     * Add one attachment to extension-based scan statistics.
     *
     * @param array $types      Statistics by extension.
     * @param string $extension File extension.
     * @param bool $transferred Transfer state.
     *
     * @return void
     */
    private function addTypeStatistics(array &$types, string $extension, bool $transferred): void
    {
        if (!isset($types[$extension])) {
            $types[$extension] = [
                'total'           => 0,
                'transferred'     => 0,
                'not_transferred' => 0,
            ];
        }

        $types[$extension]['total']++;

        if ($transferred) {
            $types[$extension]['transferred']++;
        } else {
            $types[$extension]['not_transferred']++;
        }
    }

    /**
     * Inspect an attachment and resolve its local file metadata.
     *
     * @param int $attachmentId Attachment ID.
     *
     * @return array|null Attachment metadata or null when the file is unavailable.
     */
    private function inspectAttachment(int $attachmentId): ?array
    {
        $relative_path = get_post_meta($attachmentId, '_wp_attached_file', true);
        if (!$relative_path) {
            return null;
        }

        $path = get_attached_file($attachmentId);
        if (!$path || !is_file($path)) {
            return null;
        }

        $filename = basename($path);
        $extension = strtolower((string)pathinfo($filename, PATHINFO_EXTENSION));

        $size = filesize($path);
        if ($size === false) {
            $size = 0;
        }
        $mimeType = get_post_mime_type($attachmentId);

        return [
            'attachment_id' => $attachmentId,
            'filename'      => $filename,
            'extension'     => $extension,
            'relative_path' => $relative_path,
            'mime_type'     => $mimeType ?: 'application/octet-stream',
            'size'          => $size,
        ];
    }

    /**
     * Retrieve one paginated attachment batch.
     *
     * @param int $page    Page number.
     * @param int $perPage Number of attachments per page.
     *
     * @return array Attachment IDs.
     */
    private function getAttachments(int $page, int $perPage): array
    {
        $query = new WP_Query([
            'post_type'              => 'attachment',
            'post_status'            => 'inherit',
            'posts_per_page'         => $perPage,
            'paged'                  => $page,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        return array_map('intval', $query->posts);
    }

    /**
     * Retrieve all WordPress attachments for a full scan.
     *
     * @return array Attachment IDs.
     */
    private function getAllAttachments(): array
    {
        $query = new WP_Query([
            'post_type'              => 'attachment',
            'post_status'            => 'inherit',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        return array_map('intval', $query->posts);
    }

    /**
     * Get the total number of published attachment records.
     *
     * @return int Attachment count.
     */
    private function getTotalAttachments(): int
    {
        $counts = wp_count_posts('attachment');
        return isset($counts->inherit) ? (int)$counts->inherit : 0;
    }

    /**
     * Build the persisted scan statistics.
     *
     * @return array Scan statistics.
     */
    private function getScanStatistics(): array
    {
        return [
            'summary' => $this->scanFileRepository->statistics(),
            'types'   => $this->scanFileRepository->statisticsByType(),
        ];
    }

    /**
     * Resolve the logical scanner file type from a MIME type.
     *
     * @param string $mimeType WordPress MIME type.
     *
     * @return string Scanner file type.
     */
    private function resolveFileType(string $mimeType): string
    {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        }

        if (strpos($mimeType, 'video/') === 0) {
            return 'video';
        }

        if (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        }

        if (strpos($mimeType, 'text/') === 0) {
            return 'document';
        }

        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ], true)) {
            return 'document';
        }

        if (in_array($mimeType, [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/gzip',
        ], true)) {
            return 'archive';
        }

        return 'other';
    }
}