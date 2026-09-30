<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Repositories;


use MagicalConnection\Modules\Scanner\Model\ScanFileModel;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Provide repository operations for scanner file records.
 *
 * Coordinates scan file persistence, transfer state, and aggregated
 * statistics without exposing the underlying database model to services.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ScanFileRepository
{
    private ScanFileModel $model;

    /**
     * Create the scan file repository.
     *
     * @param ScanFileModel $model Scan file model.
     *
     * @since 1.0.0
     */
    public function __construct(ScanFileModel $model)
    {
        $this->model = $model;
    }

    /**
     * Create a scan file record or update an existing one.
     *
     * Existing records are identified by attachment ID. The original
     * attachment ID and initial scan ID are preserved when updating
     * an existing record.
     *
     * @param array $data Scan file data.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function createOrUpdate(array $data): void
    {
        if (!isset($data['attachment_id'])) {
            throw new MagicalConnectionException(
                'Attachment ID is required.',
                'scanner.scan_file.attachment_id_required'
            );
        }

        $attachmentId = (int)$data['attachment_id'];
        $existing = $this->model->findByAttachmentId($attachmentId);
        $now = current_time('mysql');

        if ($existing) {
            unset($data['attachment_id']);
            unset($data['scan_find_id']);
            $data['updated_at'] = $now;
            if (!$this->model->updateByAttachmentId($attachmentId, $data)) {
                throw new MagicalConnectionException(
                    'Unable to update scan file.',
                    'scanner.scan_file.update_failed'
                );
            }
            return;
        }

        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        $inserted = $this->model->insert($data);
        if (!$inserted) {
            throw new MagicalConnectionException(
                'Unable to create scan file.',
                'scanner.scan_file.create_failed'
            );
        }
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
        return $this->model->findByAttachmentId(
            $attachmentId
        );
    }

    /**
     * Retrieve all scan file records.
     *
     * @return array Scan file records.
     *
     * @since 1.0.0
     */
    public function getAll(): array
    {
        return $this->model->findAll();
    }

    /**
     * Get aggregate scan file statistics.
     *
     * @return array Total, transferred, and non-transferred counts.
     *
     * @since 1.0.0
     */
    public function statistics(): array
    {
        $result = $this->model->getStatistics();
        if (is_null($result)) {
            return [
                'total'           => 0,
                'transferred'     => 0,
                'not_transferred' => 0,
            ];
        }
        return $result;
    }

    /**
     * Get scan file statistics grouped by file type.
     *
     * @return array Statistics grouped by file type.
     *
     * @since 1.0.0
     */
    public function statisticsByType(): array
    {
        return $this->model->getStatisticsByType();
    }

    /**
     * Get scan file statistics grouped by extension.
     *
     * Adds aggregate totals across all extensions.
     *
     * @return array Extension statistics and aggregate totals.
     *
     * @since 1.0.0
     */
    public function statisticsByExtension(): array
    {
        $statistics = $this->model->getStatisticsByExtension();

        $total = [
            'total_files'    => 0,
            'in_wordpress'   => 0,
            'in_remote'      => 0,
            'wordpress_size' => 0,
            'remote_size'    => 0,
        ];

        foreach ($statistics as $item) {
            $total['total_files'] += (int)$item['total_files'];
            $total['in_wordpress'] += (int)$item['in_wordpress'];
            $total['in_remote'] += (int)$item['in_remote'];
            $total['wordpress_size'] += (int)$item['wordpress_size'];
            $total['remote_size'] += (int)$item['remote_size'];
        }

        if ($total['total_files'] > 0) {
            $total['wordpress_percentage'] = round(
                $total['in_wordpress'] * 100 / $total['total_files'],
                1
            );

            $total['remote_percentage'] = round(
                $total['on_remote'] * 100 / $total['total_files'],
                1
            );
        } else {
            $total['wordpress_percentage'] = 0;
            $total['remote_percentage'] = 0;
        }

        return ['extension' => $statistics, 'extension_total' => $total];
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
        return $this->model->markAsTransferredById($id);
    }

    /**
     * Update the transfer state of an attachment.
     *
     * @param int  $id          Attachment ID.
     * @param bool $transferred Whether the attachment is fully transferred.
     *
     * @return bool Whether the record was updated.
     *
     * @since 1.0.0
     */
    public function markTransferredByIdAttachment(int $id, bool $transferred): bool
    {
        return $this->model->markAsTransferredByAttachmentId($id, $transferred);
    }

    /**
     * Build file-type summary statistics.
     *
     * Calculates the percentage of each file type relative to
     * the total number of scanned files.
     *
     * @return array File-type summary.
     *
     * @since 1.0.0
     */
    public function summaryByType(): array
    {
        $statistics = $this->model->getStatisticsByType();
        $total = 0;

        foreach ($statistics as $item) {
            $total += (int)$item['total'];
        }

        if ($total === 0) {
            return [];
        }

        $result = [];
        foreach ($statistics as $item) {
            $count = (int)$item['total'];
            $result[] = [
                'type'       => $item['file_type'],
                'count'      => $count,
                'percentage' => round(($count / $total) * 100, 1),
            ];
        }

        return $result;
    }

    /**
     * Find the next scan file pending transfer.
     *
     * @return array|null Pending scan file or null when none exists.
     *
     * @since 1.0.0
     */
    public function findPendingTransfer()
    {
        $result = $this->model->findPendingTransferId();
        if (is_null($result)) {
            return false;
        }
        return $result;
    }
}