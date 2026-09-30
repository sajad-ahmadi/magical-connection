<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Clients\FTP;


use MagicalConnection\Support\MagicalConnectionException;

/**
 * Manage file operations on an FTP server.
 *
 * Provides file upload, append, deletion, existence checks,
 * and remote file size operations through an active FTP connection.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class FTPFile
{
    /**
     * Manage the underlying FTP connection.
     *
     * @since 1.0.0
     */
    private FTPConnection $connection;

    /**
     * Manage FTP directory operations required by file operations.
     *
     * @since 1.0.0
     */
    private FTPDirectory $directory;

    /**
     * Create a new FTP file handler.
     *
     * @since 1.0.0
     *
     * @param FTPConnection $connection FTP connection handler.
     * @param FTPDirectory  $directory  FTP directory handler.
     */
    public function __construct(FTPConnection $connection, FTPDirectory $directory)
    {
        $this->connection = $connection;
        $this->directory = $directory;
    }

    /**
     * Upload a local file to the FTP server.
     *
     * Ensures the remote parent directory exists before uploading
     * the file in binary transfer mode.
     *
     * @since 1.0.0
     *
     * @param string $localPath  Local file path.
     * @param string $remotePath Remote FTP file path.
     *
     * @return bool True when the file is uploaded successfully.
     */
    public function upload(string $localPath, string $remotePath): bool
    {

        $ftp = $this->connection->getConnection();

        if (!file_exists($localPath)) {
            throw new MagicalConnectionException(
                'The local file does not exist.',
                'local_file_not_found'
            );
        }

        if (!is_file($localPath)) {
            throw new MagicalConnectionException(
                "Local file not found: {$localPath}",
                'ftp.local_file_not_found'
            );
        }

        if (!is_readable($localPath)) {
            throw new MagicalConnectionException(
                "Local file is not readable: {$localPath}",
                'ftp.local_file_not_readable'
            );
        }

        $directory = dirname($remotePath);
        $this->directory->ensure($directory);
        $result = ftp_put(
            $ftp,
            "/" . $remotePath,
            $localPath,
            FTP_BINARY
        );

        if (!$result) {
            throw new MagicalConnectionException(
                'Failed to upload the file to the FTP server.',
                'ftp.upload_failed'
            );
        }

        return true;
    }

    /**
     * Delete a remote FTP file.
     *
     * @since 1.0.0
     *
     * @param string $remotePath Remote FTP file path.
     *
     * @return bool True when the file is deleted successfully.
     */
    public function delete(string $remotePath): bool
    {
        $ftp = $this->connection->getConnection();

        if (!@ftp_delete($ftp, $remotePath)) {
            throw new MagicalConnectionException(
                'Unable to delete FTP file.',
                'ftp.delete_failed'
            );
        }

        return true;
    }


    /**
     * Determine whether a remote FTP file exists.
     *
     * Uses the remote file size lookup as an existence check.
     *
     * @since 1.0.0
     *
     * @param string $remotePath Remote FTP file path.
     *
     * @return bool True when the remote file exists.
     */
    public function exists(string $remotePath): bool
    {
        $ftp = $this->connection->getConnection();
        return @ftp_size($ftp, $remotePath) !== -1;
    }

    /**
     * Get the size of a remote FTP file.
     *
     * @since 1.0.0
     *
     * @param string $remotePath Remote FTP file path.
     *
     * @return int Remote file size in bytes.
     */
    public function size(string $remotePath): int
    {
        $ftp = $this->connection->getConnection();

        $size = @ftp_size($ftp, $remotePath);

        if ($size === -1) {
            throw new MagicalConnectionException(
                'Unable to get size of FTP file.',
                'ftp.size_failed'
            );
        }

        return $size;
    }

    /**
     * Append a local file to a remote FTP file.
     *
     * Creates the remote file when it does not exist and appends
     * to the existing file otherwise. Data is transferred in chunks
     * to avoid loading the entire local file into memory.
     *
     * @since 1.0.0
     *
     * @param string $localPath  Local file path.
     * @param string $remotePath Remote FTP file path.
     *
     * @return bool True when the file is appended successfully.
     */
    public function append(string $localPath, string $remotePath): bool
    {

        if (!is_file($localPath)) {
            throw new MagicalConnectionException(
                sprintf(
                    'local "%s" file does not exist.',
                    $localPath
                ),
                'local_file_not_found'
            );
        }

        $this->directory->ensure(dirname($remotePath));

        $input = @fopen($localPath, 'rb');

        if ($input === false) {
            throw new MagicalConnectionException(
                sprintf(
                    'Unable to open local file "%s".',
                    $localPath
                ),
                'ftp.open_failed'
            );
        }

        $mode = $this->exists($remotePath) ? 'ab' : 'wb';

        try {
            $output = $this->connection->openStream(
                $remotePath,
                $mode
            );

            while (!feof($input)) {
                $buffer = fread(
                    $input,
                    1024 * 1024
                );

                if ($buffer === false) {
                    throw new MagicalConnectionException(
                        sprintf(
                            'Unable to read local file "%s".',
                            $localPath
                        ),
                        'read_failed'
                    );
                }

                if ($buffer === '') {
                    continue;
                }

                $this->writeAll($output, $buffer);
            }
        } finally {
            fclose($input);
            fclose($output);
        }

        return true;
    }

    /**
     * Write all provided data to a stream.
     *
     * Continues writing until the complete data buffer has been
     * written because fwrite() may write fewer bytes than requested.
     *
     * @since 1.0.0
     *
     * @param resource $stream Output stream.
     * @param string   $data   Data to write.
     *
     * @return void
     */
    private function writeAll($stream, string $data): void
    {
        $length = strlen($data);
        $written = 0;

        while ($written < $length) {

            $result = fwrite(
                $stream,
                substr($data, $written)
            );

            if ($result === false) {
                throw new MagicalConnectionException(
                    'Unable to write data to remote file',
                    'write_failed'
                );
            }

            $written += $result;
        }
    }
}