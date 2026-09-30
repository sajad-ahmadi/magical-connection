<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Service;

use MagicalConnection\Modules\Media\Repositories\MediaReferenceRepository;
use MagicalConnection\Support\Arr;

/**
 * Manage media references between attachments and content.
 *
 * Synchronizes attachment references by discovering content that uses
 * an attachment's original or generated media URLs, as well as content
 * using the attachment as its featured image.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaReferenceService
{
    private MediaReferenceRepository $repository;

    /**
     * Create the media reference service.
     *
     * @param MediaReferenceRepository $repository Media reference repository.
     *
     * @since 1.0.0
     */
    public function __construct(MediaReferenceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Synchronize all content references for an attachment.
     *
     * Existing references are removed before the attachment URLs and
     * featured-image relationships are searched again.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Synchronization result.
     *
     * @since 1.0.0
     */
    public function syncAttachmentReferences(int $attachmentId): array
    {
        $result = [
            'attachment_id' => $attachmentId,
            'deleted'       => false,
            'found'         => 0,
            'created'       => 0,
            'content_ids'   => [],
        ];
        if ($attachmentId <= 0) {
            return $result;
        }

        $deleted = $this->repository->deleteByAttachmentId($attachmentId);
        $urls = $this->getAttachmentUrls($attachmentId);
        if (empty($urls)) {
            $result['deleted'] = $deleted;
            return $result;
        }
        $contentIds = $this->repository->findContentIdsByUrls($urls);
        $contentIds = array_merge(
            $contentIds,
            $this->repository->findFeaturedImageContentIds($attachmentId)
        );

        $contentIds = $this->normalizeIds($contentIds);
        $result['content_ids'] = $contentIds;
        $result['found'] = count($contentIds);

        foreach ($contentIds as $contentId) {
            $this->repository->create([
                'content_id'    => $contentId,
                'attachment_id' => $attachmentId,
            ]);
            $result['created']++;
        }

        return $result;
    }

    /**
     * Find attachment IDs referenced by a content item.
     *
     * @param int $contentId WordPress content ID.
     *
     * @return array Referenced attachment IDs.
     *
     * @since 1.0.0
     */
    public function getAttachmentIds(int $contentId): array
    {
        $references = $this->repository->findByContentId($contentId);
        if (empty($references)) {
            return [];
        }

        $attachmentIds = [];
        foreach ($references as $reference) {
            $attachmentId = $reference['attachment_id'] ?? 0;

            if ($attachmentId > 0) {
                $attachmentIds[] = (int)$attachmentId;
            }
        }

        return $this->normalizeIds($attachmentIds);
    }

    /**
     * Build all known URLs for an attachment.
     *
     * Includes the original attachment URL and URLs of generated
     * WordPress image sizes stored in attachment metadata.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Normalized attachment URLs.
     *
     * @since 1.0.0
     */
    private function getAttachmentUrls(int $attachmentId): array
    {
        $metadata = wp_get_attachment_metadata(
            $attachmentId
        );

        if (!is_array($metadata)) {
            return [];
        }

        $uploadDir = wp_upload_dir();
        if (!is_array($uploadDir) || empty($uploadDir['baseurl'])) {
            return [];
        }

        $urls = [];
        if (!empty($metadata['file']) && is_string($metadata['file'])) {
            $urls[] = $this->normalizeUrl(trailingslashit($uploadDir['baseurl']) . ltrim($metadata['file'], '/'));
        }

        $directory = '';
        if (!empty($metadata['file']) && is_string($metadata['file'])) {
            $directory = dirname($metadata['file']);
        }

        foreach (($metadata['sizes'] ?? []) as $size) {
            if (empty($size['file']) || !is_string($size['file'])) {
                continue;
            }

            $url = trailingslashit($uploadDir['baseurl']);

            if ($directory !== '.' && $directory !== '') {
                $url .= trailingslashit(trim($directory, '/'));
            }

            $url .= ltrim($size['file'], '/');
            $urls[] = $this->normalizeUrl($url);
        }

        return array_values(array_unique($urls));
    }

    /**
     * Normalize an attachment URL for reference matching.
     *
     * Decodes HTML entities and removes trailing slashes so equivalent
     * URL representations can be matched consistently.
     *
     * @param string $url URL to normalize.
     *
     * @return string Normalized URL.
     *
     * @since 1.0.0
     */
    private function normalizeUrl(string $url): string
    {
        return rtrim(
            html_entity_decode(
                $url,
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ),
            '/'
        );
    }

    /**
     * Normalize a list of numeric IDs.
     *
     * @param array $ids IDs to normalize.
     *
     * @return array Unique, positive, re-indexed IDs.
     *
     * @since 1.0.0
     */
    private function normalizeIds(array $ids): array
    {
        return Arr::normalizeNumbers($ids);
    }

}
