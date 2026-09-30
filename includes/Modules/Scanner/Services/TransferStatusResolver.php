<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Services;

use MagicalConnection\Modules\Media\Repositories\MediaTransferRepository;

/**
 * Resolve the transfer state of media attachments.
 *
 * Provides a scanner-facing abstraction for determining whether an attachment
 * has at least one completed media transfer.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class TransferStatusResolver
{
    private MediaTransferRepository $repository;

    /**
     * Create the transfer status resolver.
     *
     * @param MediaTransferRepository $repository Media transfer repository.
     */
    public function __construct(MediaTransferRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Determine whether an attachment has been transferred.
     *
     * The result is delegated to the media transfer repository, which checks
     * whether a completed transfer record exists for the attachment.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return bool True when a transferred record exists, otherwise false.
     */
    public function isTransferred(int $attachmentId): bool
    {
        return $this->repository->existsTransferred($attachmentId);
    }
}