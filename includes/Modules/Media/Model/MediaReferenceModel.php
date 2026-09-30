<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Model;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateMediaReferencesTable;

/**
 * Manage content-to-attachment media reference records.
 *
 * Provides persistence operations for relationships between WordPress
 * content items and their associated media attachments.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaReferenceModel
{
    private Database $database;
    private string $table;

    /**
     * Create the media reference model.
     *
     * @param Database $database Database abstraction used for persistence.
     * @param CreateMediaReferencesTable $table Media references table definition.
     *
     * @since 1.0.0
     */
    public function __construct(Database $database, CreateMediaReferencesTable $table)
    {
        $this->table = $table->tableName();
        $this->database = $database;
    }

    /**
     * Insert a media reference record.
     *
     * @param array $data Reference data to persist.
     *
     * @return int|false Inserted record ID on success, or false on failure.
     *
     * @since 1.0.0
     */
    public function insert(array $data)
    {
        return $this->database->insert(
            $this->table,
            $data
        );
    }

    /**
     * Delete media reference records matching the given conditions.
     *
     * @param array $data Conditions used to identify records.
     *
     * @return bool True when the delete operation succeeds, otherwise false.
     *
     * @since 1.0.0
     */
    public function deleteByAttachmentId(array $data): bool
    {
        return $this->database->delete($this->table, $data);
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
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE attachment_id = %d",
            $this->table,
            $attachmentId
        );
        return $this->database->getResults($sql);
    }

    /**
     * Find published content containing any of the given URLs.
     *
     * Searches post content for URL occurrences and returns the matching
     * content IDs.
     *
     * @param array $urls URLs to search for in post content.
     *
     * @return array Matching WordPress post IDs.
     *
     * @since 1.0.0
     */
    public function findContentIdsByUrls(array $urls): array
    {
        if (empty($urls)) {
            return [];
        }

        $conditions = [];
        $params = [];

        foreach ($urls as $url) {
            $conditions[] = 'post_content LIKE %s';
            $params[] = '%' . $this->database->wpdb()->esc_like($url) . '%';
        }

        $where = implode(' OR ', $conditions);

        $posts = $this->database->wpdb()->posts;

        $sql = $this->database->prepare(
            " SELECT ID FROM %i WHERE post_status = 'publish'
              AND post_type NOT IN (
                  'revision',
                  'nav_menu_item'
              )
              AND ({$where})
            ",
            $posts,
            ...$params
        );
        $results = $this->database->wpdb()->get_col($sql);
        return array_map('absint', $results ?: []);
    }

    /**
     * Find content items using an attachment as their featured image.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Content IDs using the attachment as a featured image.
     *
     * @since 1.0.0
     */
    public function findFeaturedImageContentIds(int $attachmentId): array
    {
        $post_meta = $this->database->wpdb()->postmeta;
        $sql = $this->database->prepare(
            "
                SELECT post_id
                FROM %i
                WHERE meta_key = '_thumbnail_id'
                  AND meta_value = %d
                ",
            $this->table,
            $attachmentId
        );

        $results = $this->database->wpdb()->get_col($sql);
        return array_map('absint', $results ?: []);
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
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE content_id = %d",
            $this->table,
            $contentId
        );
        return $this->database->getResults($sql);
    }
}