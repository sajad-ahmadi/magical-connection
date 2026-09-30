<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Service;

use MagicalConnection\Modules\Connection\Repositories\ConnectionRepository;
use MagicalConnection\Modules\Connection\Services\ConnectionsManager;
use MagicalConnection\Modules\Media\Repositories\MediaReferenceRepository;
use MagicalConnection\Modules\Media\Repositories\MediaTransferRepository;
use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Support\AjaxStream;
use MagicalConnection\Support\MagicalConnectionException;
use WP_Post;

/**
 * Manage media transfer and restore operations.
 *
 * Coordinates attachment file discovery, remote transfers, transfer
 * persistence, media restoration, and related reference state.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaTransferService
{


    private ConnectionsManager $connectionsManager;

    private MediaTransferRepository $mediaTransferRepository;
    private MediaReferenceRepository $mediaReferenceRepository;
    private ScanFileRepository $scanFileRepository;

    /**
     * Create the media transfer service.
     *
     * @param ConnectionsManager $connectionsManager Connection client manager.
     * @param MediaTransferRepository $mediaTransferRepository Media transfer repository.
     * @param MediaReferenceRepository $mediaReferenceRepository Media reference repository.
     * @param ScanFileRepository $scanFileRepository Scan file repository.
     *
     * @since 1.0.0
     */
    public function __construct(
        ConnectionsManager       $connectionsManager,
        MediaTransferRepository  $mediaTransferRepository,
        MediaReferenceRepository $mediaReferenceRepository,
        ScanFileRepository $scanFileRepository
    )
    {
        $this->connectionsManager = $connectionsManager;
        $this->mediaTransferRepository = $mediaTransferRepository;
        $this->mediaReferenceRepository = $mediaReferenceRepository;
        $this->scanFileRepository = $scanFileRepository;
    }

    /**
     * Get attachment information and available connections.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Attachment metadata and connection information.
     *
     * @since 1.0.0
     */
    public function getInfo(int $attachmentId): array
    {
        $attachment = get_post($attachmentId);
        if (!$attachment instanceof WP_Post || $attachment->post_type !== 'attachment') {
            throw new MagicalConnectionException(
                'The requested attachment was not found.',
                'media.attachment.not_found'
            );
        }

        $file = get_attached_file($attachmentId);
        if (!$file || !is_file($file)) {
            throw new MagicalConnectionException(
                'The attachment file was not found on the WordPress host.',
                'media.attachment.file_not_found'
            );
        }

        $metadata = wp_get_attachment_metadata($attachmentId);
        $isImage = wp_attachment_is_image($attachmentId);
        $hosts = $this->getHosts();

        $sizes = [];

        if ($isImage) {
            foreach (($metadata['sizes'] ?? []) as $sizeName => $size) {
                if (empty($size['file'])) {
                    continue;
                }

                $sizes[$sizeName] = $size;
                $sizes[$sizeName]['url'] = dirname(wp_get_attachment_url($attachmentId)) . '/' . $size['file'];
            }
        }

        return [
            'media' => [
                'id'         => $attachmentId,
                'type'       => strtoupper(pathinfo($file, PATHINFO_EXTENSION)),
                'name'       => wp_basename($file),
                'mime'       => get_post_mime_type($attachmentId),
                'size'       => filesize($file),
                'url'        => wp_get_attachment_url($attachmentId),
                'dir_upload' => str_replace(wp_basename($file), '', $file),
                'date'       => get_the_date('Y-m-d H:i:s', $attachmentId),
                'is_image'   => $isImage,
                'width'      => $metadata['width'] ?? null,
                'height'     => $metadata['height'] ?? null,
                'sizes'      => $sizes,
            ],
            'hosts' => $hosts,
        ];
    }

    /**
     * Get configured connection hosts.
     *
     * @return array Available connection information.
     *
     * @since 1.0.0
     */
    private function getHosts(): array
    {

        $hosts = [];
        foreach ($this->connectionsManager->getConnections() as $item) {
            $hosts[] = [
                'id'       => $item->getId(),
                'name'     => $item->getName(),
                'protocol' => $item->getProtocol(),
                'host'     => $item->getHost(),
                'domain'   => $item->getDomain(),
                'port'     => $item->getPort(),
            ];
        }
        return $hosts;
    }

    /**
     * Get all physical files belonging to an attachment.
     *
     * Includes the original attachment file and generated WordPress
     * image sizes when they exist on the local filesystem.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return array Files available for transfer.
     *
     * @since 1.0.0
     */
    public function getFiles(int $attachmentId): array
    {
        $attachment = get_post($attachmentId);
        if (!$attachment instanceof WP_Post || $attachment->post_type !== 'attachment') {
            throw new MagicalConnectionException(
                'The requested attachment was not found.',
                'media.attachment.not_found'
            );
        }

        $original = get_attached_file($attachmentId);
        if (!$original || !is_file($original)) {
            throw new MagicalConnectionException(
                'The attachment file was not found.',
                'media.attachment.file_not_found'
            );
        }

        $metadata = wp_get_attachment_metadata($attachmentId);

        $files = [
            [
                'type'          => 'original',
                'size'          => 'original',
                'path'          => $original,
                'relative_path' => $this->getRelativePath($metadata['file']),
            ],
        ];

        if (!wp_attachment_is_image($attachmentId)) {
            return $files;
        }

        $relative_directory = dirname($metadata['file']);
        foreach (($metadata['sizes'] ?? []) as $sizeName => $size) {

            if (empty($size['file'])) {
                continue;
            }

            $path = dirname($original) . DIRECTORY_SEPARATOR . $size['file'];

            if (!is_file($path)) {
                continue;
            }

            $relative_path = $this->getRelativePath($relative_directory . '/' . $size['file']);
            $files[] = [
                'type'          => 'size',
                'size'          => $sizeName,
                'path'          => $path,
                'relative_path' => $relative_path,
            ];
        }

        return $files;
    }

    /**
     * Transfer all pending files of an attachment to a connection.
     *
     * Already transferred files are skipped. Each successfully transferred
     * file is persisted as a transfer record and the attachment is marked
     * transferred only after all required files have completed.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param int $connectionId Connection ID used for the transfer.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function transfer(int $attachmentId, int $connectionId): void
    {
        $remote_conn = null;
        try {
            $file_error = [];
            $files = $this->getFiles($attachmentId);
            $files = $this->mediaTransferRepository->existsTransferredFullAttachment($attachmentId, $files);
            if (empty($files)) {
                throw new MagicalConnectionException(
                    'Media has already been transferred.',
                    'already_transferred'
                );
            }

            $remote_conn = $this->connectionsManager->clientById($connectionId);
            $remote_conn->connect();
            foreach ($files as $file) {
                $file_error = $file;
                mc_stream()->log('Uploading file', [
                    'event_method' => 'transfer',
                    'status'       => 'uploading',
                    'data'         => $file,
                ]);

                $remote_path = $remote_conn->config()->getFullPath($file['relative_path']);
                $remote_conn->file()->upload($file['path'], $remote_path);

                $data = [
                    'attachment_id' => $attachmentId,
                    'connection_id' => $connectionId,
                    'file_type'     => $file['type'],
                    'media_size'    => $file['size'],
                    'local_path'    => $file['path'],
                    'remote_path'   => $remote_path,
                    'remote_url'    => $remote_conn->config()->getFullUrl($file['relative_path']),
                    'status'        => 'transferred',
                ];

                $this->mediaTransferRepository->create($data);
                $this->scanFileRepository->markTransferredByIdAttachment($attachmentId, true);
                mc_stream()->log('Uploaded file', [
                    'event_method' => 'transfer',
                    'status'       => 'done',
                    'data'         => $data,
                ]);
            }
        } catch (MagicalConnectionException $e) {
            mc_stream()->log('error file', [
                'event_method' => 'transfer',
                'status'       => 'failed',
                'data'         => $file_error,
            ]);
            throw $e;
        } finally {
            if ($remote_conn !== null) {
                $remote_conn->disconnect();
            }
        }
    }

    /**
     * Build a WordPress-relative path for a media file.
     *
     * @param string $metadataFile Relative path stored in attachment metadata.
     *
     * @return string Path relative to the WordPress content directory.
     *
     * @since 1.0.0
     */
    private function getRelativePath(string $metadataFile): string
    {
        $uploadDir = wp_upload_dir();
        $contentPath = wp_normalize_path(WP_CONTENT_DIR);
        $uploadPath = wp_normalize_path($uploadDir['basedir']);
        $uploadRelativePath = trim(
            str_replace(
                $contentPath . '/',
                '',
                $uploadPath
            ),
            '/'
        );

        return $uploadRelativePath . '/' . ltrim(str_replace('\\', '/', $metadataFile), '/');
    }

    /**
     * Restore transferred files of an attachment to the local filesystem.
     *
     * Existing local files are preserved. After all missing files have been
     * restored, transfer and reference records are removed and the scan file
     * is marked as not transferred.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return bool True when restoration completes successfully.
     *
     * @since 1.0.0
     */
    public function restore(int $attachmentId): bool
    {
        $transfers = $this->mediaTransferRepository->findByAttachmentId($attachmentId);

        if (empty($transfers)) {
            throw new MagicalConnectionException(
                'No transferred files were found for this attachment.',
                'media.restore.transfers_not_found'
            );
        }

        foreach ($transfers as $transfer) {

            $localPath = $this->getLocalPath($transfer);
            if (is_file($localPath)) {
                continue;
            }
            $this->restoreFile($transfer['remote_url'], $localPath);
        }

        $this->mediaTransferRepository->deleteByAttachmentId($attachmentId);
        $this->mediaReferenceRepository->deleteByAttachmentId($attachmentId);
        $this->scanFileRepository->markTransferredByIdAttachment($attachmentId, false);

        return true;
    }


    /**
     * Resolve the local path of a transferred file.
     *
     * @param array $transfer Transfer record.
     *
     * @return string Local file path.
     *
     * @since 1.0.0
     */
    private function getLocalPath(array $transfer): string
    {
        if (!isset($transfer['local_path']) || !is_string($transfer['local_path']) || $transfer['local_path'] === '') {
            throw new MagicalConnectionException(
                'Local file path is not available.',
                'media.restore.local_path_not_available'
            );
        }

        return $transfer['local_path'];
    }

    /**
     * Restore a remote file to the local filesystem.
     *
     * Downloads the file to a temporary path first and moves it into
     * its final location only after a successful HTTP response.
     *
     * @param string $remoteUrl Remote file URL.
     * @param string $localPath Destination path on the WordPress host.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function restoreFile(string $remoteUrl, string $localPath): void
    {
        if ($remoteUrl === '') {
            throw new MagicalConnectionException(
                'Remote file URL is empty.',
                'media.restore.remote_url_empty'
            );
        }

        $directory = dirname($localPath);

        if (!is_dir($directory) && !wp_mkdir_p($directory)) {
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to create directory "%s".',
                    $directory
                ),
                'media.restore.directory_create_failed'
            );
        }


        $temporaryPath = $localPath . '.tmp';

        if (is_file($temporaryPath)) {
            @unlink($temporaryPath);
        }

        $response = wp_remote_get(
            $remoteUrl,
            [
                'timeout'  => 300,
                'stream'   => true,
                'filename' => $temporaryPath,
            ]
        );

        if (is_wp_error($response)) {
            @unlink($temporaryPath);
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to download "%s": %s',
                    $remoteUrl,
                    $response->get_error_message()
                ),
                'media.restore.download_failed'
            );
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        if ($statusCode < 200 || $statusCode >= 300) {
            @unlink($temporaryPath);
            throw new MagicalConnectionException(
                sprintf(
                    'Remote server returned HTTP status %d for "%s".',
                    $statusCode,
                    $remoteUrl
                ),
                'media.restore.remote_http_error'
            );
        }

        if (!is_file($temporaryPath)) {
            throw new MagicalConnectionException(
                sprintf(
                    'Downloaded file was not created: "%s".',
                    $temporaryPath
                ),
                'media.restore.temporary_file_not_created'
            );
        }

        if (is_file($localPath)) {
            @unlink($temporaryPath);
            return;
        }

        if (!rename($temporaryPath, $localPath)) {
            @unlink($temporaryPath);
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to move restored file to "%s".',
                    $localPath
                ),
                'media.restore.file_move_failed'
            );
        }
    }
}