<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\FTP;

use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage directory operations on an FTP server.
 *
 * Provides directory existence checks, creation, deletion,
 * listing, and path normalization for an active FTP connection.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class FTPDirectory
{
    /**
     * Manage the underlying FTP connection.
     *
     * @since 1.0.0
     */
    private FTPConnection $connection;

    /**
     * Create a new FTP directory handler.
     *
     * @since 1.0.0
     *
     * @param FTPConnection $connection FTP connection handler.
     */
    public function __construct(FTPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Determine whether an FTP directory exists.
     *
     * Attempts to change the current FTP working directory to the
     * requested path. A successful directory change indicates that
     * the directory exists.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return bool True when the directory exists.
     */
    public function exists(string $path): bool
    {
        $ftp = $this->connection->getConnection();

        return @ftp_chdir($ftp, $path);
    }

    /**
     * Create an FTP directory and any missing parent directories.
     *
     * Existing directories are left unchanged. Root and empty paths
     * are treated as already available.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return bool True when the directory exists or is created successfully.
     */
    public function create(string $path): bool
    {
        $ftp = $this->connection->getConnection();

        $path = $this->normalizePath($path);

        if ($path === '' || $path === '/') {
            return true;
        }

        if ($this->exists($path)) {
            return true;
        }

        $parts = explode('/', trim($path, '/'));

        $current = '';

        foreach ($parts as $part) {

            if ($part === '') {
                continue;
            }

            $current .= '/' . $part;

            if ($this->exists($current)) {
                continue;
            }

            if (!@ftp_mkdir($ftp, $current)) {
                throw new MagicalConnectionException(
                    sprintf(
                        'Unable to create FTP directory "%s".',
                        $current
                    ),
                    'ftp.directory_create_failed'
                );
            }
        }

        return true;
    }

    /**
     * Ensure that an FTP directory exists.
     *
     * Returns successfully when the directory already exists;
     * otherwise creates the directory and any missing parents.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return bool True when the directory exists or is created successfully.
     */
    public function ensure(string $path): bool
    {
        if ($this->exists($path)) {
            return true;
        }

        return $this->create($path);
    }

    /**
     * Delete an FTP directory.
     *
     * The directory must exist before deletion. The FTP server may
     * reject the operation when the directory is not empty.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return bool True when the directory is deleted successfully.
     */
    public function delete(string $path): bool
    {
        $ftp = $this->connection->getConnection();

        if (!$this->exists($path)) {
            throw new MagicalConnectionException(
                sprintf(
                    'FTP directory "%s" does not exist.',
                    $path
                ),
                'ftp.directory_not_found'
            );
        }

        if (!@ftp_rmdir($ftp, $path)) {
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to delete FTP directory "%s".',
                    $path
                ),
                'ftp.directory_delete_failed'
            );
        }

        return true;
    }

    /**
     * List entries in an FTP directory.
     *
     * Returns the paths reported by the FTP server without additional
     * metadata.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return array Directory entry paths.
     */
    public function list(string $path = '.'): array
    {
        $ftp = $this->connection->getConnection();

        $files = @ftp_nlist($ftp, $path);

        if ($files === false) {
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to list FTP directory "%s".',
                    $path
                ),
                'ftp.directory_list_failed'
            );
        }

        return $files;
    }

    /**
     * List entries in an FTP directory with detailed metadata.
     *
     * Uses the FTP MLSD command when supported by the remote server.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return array Directory entries with metadata.
     */
    public function listDetailed(string $path = '.'): array
    {
        $ftp = $this->connection->getConnection();

        $items = @ftp_mlsd($ftp, $path);

        if ($items === false) {
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to list FTP directory "%s".',
                    $path
                ),
                'ftp.directory_list_failed'
            );
        }

        return $items;
    }

    /**
     * Normalize an FTP directory path.
     *
     * Converts Windows separators to FTP-style separators, removes
     * duplicate separators, trims surrounding whitespace, and removes
     * trailing separators except for the root path.
     *
     * @since 1.0.0
     *
     * @param string $path FTP directory path.
     *
     * @return string Normalized FTP directory path.
     */
    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        $path = str_replace('\\', '/', $path);

        $path = preg_replace(
            '#/+#',
            '/',
            $path
        );

        if ($path === '/') {
            return '/';
        }

        return rtrim($path, '/');
    }
}