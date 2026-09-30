<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Service;

use MagicalConnection\Modules\Media\Repositories\MediaTransferRepository;

/**
 * Resolve transferred media files to their remote URLs.
 *
 * Uses transferred media records to replace original attachment URLs
 * and generated image-size URLs when a remote copy is available.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaUrlService
{

    /**
     * Cached transfer records grouped by attachment ID and media size.
     *
     * @var array
     */
    private array $transferMap = [];


    private MediaTransferRepository $repository;

    /**
     * Create the media URL service.
     *
     * @param MediaTransferRepository $repository Transfer record repository.
     *
     * @since 1.0.0
     */
    public function __construct(MediaTransferRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Preload transferred media records for the given attachments.
     *
     * Existing entries in the internal cache are not queried again.
     *
     * @param array $attachmentIds Attachment IDs to preload.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function prepare(array $attachmentIds): void
    {
        if (empty($attachmentIds)) {
            return;
        }

        $missingIds = [];
        foreach ($attachmentIds as $attachmentId) {
            if (!array_key_exists($attachmentId, $this->transferMap)) {
                $missingIds[] = $attachmentId;
            }
        }


        if (empty($missingIds)) {
            return;
        }

        $records = $this->repository->findByAttachmentIds(
            $missingIds
        );

        foreach ($missingIds as $attachmentId) {
            $this->transferMap[$attachmentId] = [];
        }
        $this->buildMap($records);
    }

    /**
     * Get the remote URL for an attachment's original file.
     *
     * Returns the original WordPress URL when the attachment has not
     * been successfully transferred or has no valid remote URL.
     *
     * @param int    $attachmentId Attachment ID.
     * @param string $originalUrl  Original WordPress attachment URL.
     *
     * @return string Resolved remote URL or the original URL.
     *
     * @since 1.0.0
     */
    public function getUrl(int $attachmentId, string $originalUrl): string
    {

        $this->loadTransfers($attachmentId);

        $transfer = $this->transferMap[$attachmentId]['original'] ?? null;

        if (!$transfer || empty($transfer['remote_url'])) {
            return $originalUrl;
        }

        return $transfer['remote_url'];
    }

    /**
     * Get the remote URL and dimensions for a generated image size.
     *
     * Returns the same structure expected by WordPress image-size
     * resolution logic when a transferred file is available.
     *
     * @param int    $attachmentId Attachment ID.
     * @param string $size         Registered image size name.
     *
     * @return array|null Image URL, width, height and crop flag,
     *                               or null when unavailable.
     *
     * @since 1.0.0
     */
    public function getImageSize(int $attachmentId, $size): ?array
    {

        if (!is_string($size)) {
            return null;
        }
        $this->loadTransfers($attachmentId);
        $transfer = $this->transferMap[$attachmentId][$size] ?? null;

        if (!$transfer) {
            return null;
        }

        if ($transfer['status'] !== 'transferred' || empty($transfer['remote_url'])) {
            return null;
        }

        $metadata = wp_get_attachment_metadata(
            $attachmentId
        );

        $sizeData = $metadata['sizes'][$size] ?? null;

        if (!is_array($sizeData)) {
            return null;
        }

        return [
            $transfer['remote_url'],
            (int)($sizeData['width'] ?? 0),
            (int)($sizeData['height'] ?? 0),
            true,
        ];
    }

    /**
     * Build the transfer cache from repository records.
     *
     * Only successfully transferred records with a valid remote URL
     * are added to the cache.
     *
     * @param array $records Transfer records.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function buildMap(array $records): void
    {
        foreach ($records as $record) {
            $attachmentId = (int)(
                $record['attachment_id'] ?? 0
            );

            $mediaSize = $record['media_size'] ?? null;
            if ($attachmentId <= 0 || !is_string($mediaSize) || $mediaSize === '') {
                continue;
            }
            if (($record['status'] ?? null) !== 'transferred') {
                continue;
            }
            if (empty($record['remote_url'])) {
                continue;
            }
            $this->transferMap[$attachmentId][$mediaSize] = $record;
        }

    }

    /**
     * Load transfer records for an attachment into the cache.
     *
     * The repository is queried only once for each attachment ID.
     *
     * @param int $attachmentId Attachment ID.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function loadTransfers(int $attachmentId): void
    {
        if (array_key_exists($attachmentId, $this->transferMap)) {
            return;
        }

        $transfers = $this->repository->findByAttachmentId($attachmentId);
        $this->transferMap[$attachmentId] = [];
        foreach ($transfers as $transfer) {
            if (empty($transfer['media_size']) || $transfer['status'] !== 'transferred') {
                continue;
            }
            $this->transferMap[$attachmentId][$transfer['media_size']] = $transfer;
        }
    }
}