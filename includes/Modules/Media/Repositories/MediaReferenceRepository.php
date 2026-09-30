<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Repositories;

use MagicalConnection\Modules\Media\Model\MediaReferenceModel;

/**
 * Provide repository operations for media references.
 *
 * Acts as the persistence boundary for content-to-attachment media
 * reference records.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaReferenceRepository
{
    private MediaReferenceModel $model;


    /**
     * Create the media reference repository.
     *
     * @param MediaReferenceModel $model Media reference persistence model.
     *
     * @since 1.0.0
     */
    public function __construct(MediaReferenceModel $model)
    {
        $this->model = $model;
    }

    /**
     * Create a media reference record.
     *
     * @param array $data Reference data to persist.
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
     * Delete all media references associated with an attachment.
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
     * Find media references associated with an attachment.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Matching media reference records.
     *
     * @since 1.0.0
     */
    public function findByAttachmentId(int $attachmentId): array
    {
        return $this->model->findByAttachmentId($attachmentId);
    }

    /**
     * Find content IDs containing any of the given media URLs.
     *
     * @param array $urls Media URLs to search for.
     *
     * @return array Matching WordPress content IDs.
     *
     * @since 1.0.0
     */
    public function findContentIdsByUrls(array $urls): array
    {
        return $this->model->findContentIdsByUrls($urls);
    }

    /**
     * Find content IDs using an attachment as their featured image.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Matching WordPress content IDs.
     *
     * @since 1.0.0
     */
    public function findFeaturedImageContentIds(int $attachmentId): array
    {
        return $this->model->findFeaturedImageContentIds($attachmentId);
    }

    /**
     * Find media references associated with a content item.
     *
     * @param int $contentId WordPress content ID.
     *
     * @return array Matching media reference records.
     *
     * @since 1.0.0
     */
    public function findByContentId(int $contentId): array
    {
        return $this->model->findByContentId($contentId);
    }
}