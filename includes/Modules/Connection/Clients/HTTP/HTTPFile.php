<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\HTTP;

use CURLFile;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage file operations through the HTTP API.
 *
 * Provides file upload and deletion operations using the
 * configured HTTP connection.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class HTTPFile
{
    /**
     * Manage the underlying HTTP connection.
     *
     * @since 1.0.0
     */
    private HTTPConnection $connection;

    /**
     * Create a new HTTP file handler.
     *
     * @since 1.0.0
     *
     * @param HTTPConnection $connection HTTP connection handler.
     */
    public function __construct(HTTPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Upload a local file through the HTTP API.
     *
     * Validates the local file and sends it as a multipart upload
     * together with the requested remote path.
     *
     * @since 1.0.0
     *
     * @param string $localPath  Local file path.
     * @param string $remotePath Remote destination path.
     *
     * @return array Parsed API response.
     */
    public function upload(string $localPath, string $remotePath): array
    {

        if (!is_file($localPath)) {
            throw new MagicalConnectionException(
                'The local file does not exist.',
                'local_file_not_found'
            );
        }

        return $this->connection->request(
            'POST',
            'upload',
            [
                'remote_path' => $remotePath,
            ],
            [
                'file' => new CURLFile(
                    $localPath,
                    mime_content_type($localPath) ?: 'application/octet-stream',
                    basename($localPath)
                ),
            ]
        );
    }

    /**
     * Delete a remote file through the HTTP API.
     *
     * @since 1.0.0
     *
     * @param string $remotePath Remote file path.
     *
     * @return array Parsed API response.
     */
    public function delete(string $remotePath): array
    {
        return $this->connection->request(
            'POST',
            'delete',
            [
                'remote_path' => $remotePath,
            ]
        );
    }

}