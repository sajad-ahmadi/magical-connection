<?php
declare(strict_types=1);
namespace MagicalConnection\Modules\Media\Model;

use MagicalConnection\Database\Database;
use MagicalConnection\Database\Migrations\CreateMediaTransfersTable;

/**
 * Manage media transfer records.
 *
 * Provides persistence operations for tracking media transfers associated
 * with attachments, including transferred status and media size variants.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaTransferModel
{
    private Database $database;
    private string $table;

    /**
     * Create the media transfer model.
     *
     * @param Database $database Database abstraction used for persistence.
     * @param CreateMediaTransfersTable $table Media transfers table definition.
     *
     * @since 1.0.0
     */
    public function __construct(Database $database, CreateMediaTransfersTable $table)
    {
        $this->table = $table->tableName();
        $this->database = $database;
    }

    /**
     * Insert a media transfer record.
     *
     * @param array $data Transfer data to persist.
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
     * Delete media transfer records matching the given conditions.
     *
     * @param array $data Conditions used to identify records.
     *
     * @return bool True when the delete operation succeeds, otherwise false.
     *
     * @since 1.0.0
     */
    public function deleteByAttachmentId(array $data): bool {
        return $this->database->delete($this->table, $data);
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
    public function findByAttachmentId(int $attachmentId): array {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE `attachment_id` = %d",
            $this->table,
            $attachmentId
        );
        return $this->database->getResults($sql);
    }

    /**
     * Find a transferred media record for a specific attachment and size.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param string $mediaSize Media size identifier.
     *
     * @return array Matching transfer record, or null when not found.
     *
     * @since 1.0.0
     */
    public function findByAttachmentAndSize(int $attachmentId, string $mediaSize): ?array {
        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE `attachment_id` = %d AND media_size = %s AND status = 'transferred'",
            $this->table,
            $attachmentId,
            $mediaSize
        );
        return $this->database->getRow($sql);
    }

    /**
     * Find transferred records for multiple attachments.
     *
     * Duplicate and invalid attachment IDs are removed before querying.
     *
     * @param array $attachmentIds WordPress attachment IDs.
     *
     * @return array Matching transferred records.
     *
     * @since 1.0.0
     */
    public function findByAttachmentIds(array $attachmentIds): array {
        if (empty($attachmentIds)) {
            return [];
        }

        $attachmentIds = array_map('absint', $attachmentIds);
        $attachmentIds = array_values(
            array_unique(
                array_filter($attachmentIds)
            )
        );

        if (empty($attachmentIds)) {
            return [];
        }

        $placeholders = implode(
            ',',
            array_fill(
                0,
                count($attachmentIds),
                '%d'
            )
        );


        $sql = $this->database->prepare(
            "SELECT * FROM %i WHERE attachment_id IN ($placeholders) AND status = 'transferred'",
            $this->table,
            ...$attachmentIds
        );

        return $this->database->getResults($sql);
    }

    /**
     * Determine whether an attachment has any transferred media record.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return bool True when at least one transferred record exists.
     *
     * @since 1.0.0
     */
    public function existsTransferred(int $attachmentId): bool {
        $sql = $this->database->prepare(
            "SELECT id FROM %i WHERE attachment_id = %d AND status = %s LIMIT 1",
            $this->table,
            $attachmentId,
            'transferred'
        );
        return (bool) $this->database->wpdb()->get_var($sql);
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
    public function existsTransferredBySize(int $attachmentId, string $media_size)
    {
        $sql = $this->database->prepare(
            "SELECT id FROM %i WHERE attachment_id = %d AND status = %s AND media_size = %s LIMIT 1",
            $this->table,
            $attachmentId,
            'transferred',
            $media_size
        );

        return (bool) $this->database->wpdb()->get_var($sql);
    }
}