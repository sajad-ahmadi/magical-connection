<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Repositories;

use MagicalConnection\Modules\Media\Model\MediaTransferModel;
use MagicalConnection\Support\Arr;

/**
 * Provide repository operations for media transfer records.
 *
 * Acts as the persistence boundary for media transfer records and provides
 * higher-level queries used to determine transfer state for attachments.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaTransferRepository
{
    private MediaTransferModel $model;

    /**
     * Create the media transfer repository.
     *
     * @param MediaTransferModel $model Media transfer persistence model.
     *
     * @since 1.0.0
     */
    public function __construct(MediaTransferModel $model)
    {
        $this->model = $model;
    }

    /**
     * Create a media transfer record.
     *
     * @param array $data Transfer data to persist.
     *
     * @return int|false Inserted record ID on success, or false on failure.
     *
     * @since 1.0.0
     */
    public function create(array $data)
    {
        return $this->model->insert($data);
    }

    /**
     * Delete all transfer records associated with an attachment.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return bool True when the delete operation succeeds, otherwise false.
     *
     * @since 1.0.0
     */
    public function deleteByAttachmentId(int $attachmentId): bool
    {
        return $this->model->deleteByAttachmentId(
            [
                'attachment_id' => $attachmentId,
            ]
        );
    }

    /**
     * Find transfer records associated with an attachment.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Matching transfer records.
     *
     * @since 1.0.0
     */
    public function findByAttachmentId(int $attachmentId): array
    {
        return $this->model->findByAttachmentId(
            $attachmentId
        );
    }

    /**
     * Find a transferred record for a specific attachment and media size.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param string $mediaSize Media size identifier.
     *
     * @return array|null Matching transfer record, or null when not found.
     *
     * @since 1.0.0
     */
    public function findByAttachmentAndSize(int $attachmentId, string $mediaSize): ?array
    {
        return $this->model->findByAttachmentAndSize(
            $attachmentId,
            $mediaSize
        );
    }

    /**
     * Find transferred records for multiple attachments.
     *
     * @param array $attachmentIds WordPress attachment IDs.
     *
     * @return array Matching transferred records.
     *
     * @since 1.0.0
     */
    public function findByAttachmentIds(array $attachmentIds): array
    {
        return $this->model->findByAttachmentIds($attachmentIds);
    }

    /**
     * Determine whether an attachment has any transferred media record.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return bool True when a transferred record exists.
     *
     * @since 1.0.0
     */
    public function existsTransferred(int $attachmentId)
    {
        return $this->model->existsTransferred($attachmentId);
    }

    /**
     * Determine whether a specific media size has been transferred.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param string $mediaSize Media size identifier.
     *
     * @return bool True when a transferred record exists for the size.
     *
     * @since 1.0.0
     */
    public function existsTransferredBySize(int $attachmentId, string $media_size): bool
    {
        return $this->model->existsTransferredBySize($attachmentId, $media_size);
    }

    /**
     * Remove files that have already been fully transferred from a file list.
     *
     * A file is considered transferred when its media size matches a
     * transferred record associated with the attachment.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param array $files Files to check against transfer records.
     *
     * @return array Files that still require transfer.
     *
     * @since 1.0.0
     */
    public function existsTransferredFullAttachment(int $attachmentId, array $files): array
    {
        $result = $this->findByAttachmentId($attachmentId);
        if (empty($result)) return $files;

        foreach ($files as $key => $file) {
            foreach ($result as $resultItem) {
                if ($resultItem['status'] == 'transferred' && $resultItem['media_size'] == $file['size']) {
                    unset($files[$key]);
                }
            }
        }

        return Arr::normalizeNumbers($files);
    }
}