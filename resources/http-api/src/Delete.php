<?php

declare(strict_types=1);

namespace MagicalConnection\HttpAPI;

class Delete
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = dirname(__DIR__, 2);
    }

    public function file(string $remotePath): array
    {
        $destination = $this->resolvePath($remotePath);

        if (!file_exists($destination)) {
            Response::error('File does not exist.', 404, 'delete.file_not_found');
        }

        if (!is_file($destination)) {
            Response::error('The specified path is not a file.', 422, 'delete.invalid_file');
        }

        if (!unlink($destination)) {
            Response::error('Unable to delete file.', 500, 'delete.failed');
        }

        return [
            'deleted' => true,
            'path'    => $remotePath,
        ];
    }

    private function resolvePath(string $remotePath): string
    {
        $remotePath = str_replace(
            ['\\', "\0"],
            ['/', ''],
            $remotePath
        );

        $remotePath = ltrim($remotePath, '/');

        if ($remotePath === '') {
            Response::error(
                'Remote path is invalid.',
                422,
                'delete.invalid_remote_path'
            );
        }

        if (
            strpos($remotePath, '../') !== false ||
            strpos($remotePath, '..\\') !== false
        ) {
            Response::error(
                'Remote path is not allowed.',
                403,
                'delete.path_not_allowed'
            );
        }

        $basePath = realpath($this->storagePath);

        if ($basePath === false) {
            Response::error(
                'Storage path does not exist.',
                500,
                'storage.path_not_found'
            );
        }

        $destination = realpath(
            $basePath . DIRECTORY_SEPARATOR . $remotePath
        );

        if ($destination === false) {
            return $basePath . DIRECTORY_SEPARATOR . $remotePath;
        }

        if (
            $destination !== $basePath &&
            strpos(
                $destination,
                $basePath . DIRECTORY_SEPARATOR
            ) !== 0
        ) {
            Response::error(
                'Remote path is not allowed.',
                403,
                'delete.path_not_allowed'
            );
        }

        return $destination;
    }
}