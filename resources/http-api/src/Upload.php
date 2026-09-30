<?php

declare(strict_types=1);

namespace MagicalConnection\HttpAPI;

class Upload
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = dirname(__DIR__, 3);
    }

    public function store(array $file, string $remotePath): array
    {
        $tmpName = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
        $originalName = isset($file['name']) ? (string)$file['name'] : '';

        if ($originalName === '') {
            Response::error('Uploaded filename is missing.', 422, 'upload.filename_missing');
        }

        if ($tmpName === '') {
            Response::error('Uploaded temporary file is missing.', 422, 'upload.temp_file_missing');
        }

        if (!is_uploaded_file($tmpName)) {
            Response::error('Invalid uploaded file.', 422, 'upload.invalid_file');
        }

        $destination = $this->resolvePath($remotePath);
        $directory = dirname($destination);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                Response::error('Unable to create destination directory.', 500, 'upload.directory_create_failed');
            }
        }

        if (!move_uploaded_file($tmpName, $destination)) {
            Response::error('Unable to store uploaded file.', 500, 'upload.store_failed');
        }

        return [
            'filename'          => basename($destination),
            'original_filename' => $originalName,
            'path'              => $remotePath,
            'size'              => filesize($destination),
        ];
    }


    private function generateFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = 'mc_' . bin2hex(random_bytes(16));

        if ($extension !== '') {
            $extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $extension));
            if ($extension !== '') {
                $filename .= '.' . $extension;
            }
        }

        return $filename;
    }


    private function resolvePath(string $remotePath): string
    {
        $remotePath = str_replace(['\\', "\0"], ['/', ''], $remotePath);

        $remotePath = ltrim($remotePath, '/');

        if ($remotePath === '') {
            Response::error('Remote path is invalid.', 422, 'upload.invalid_remote_path');
        }

        if (strpos($remotePath, '../') !== false ||
            strpos($remotePath, '/../') !== false ||
            strpos($remotePath, '..\\') !== false
        ) {
            Response::error('Remote path is not allowed.', 403, 'upload.path_not_allowed');
        }

        $basePath = realpath($this->storagePath);

        if ($basePath === false) {
            Response::error('Storage path does not exist.', 500, 'storage.path_not_found');
        }

        $destination = "../" . $remotePath;

        return $destination;
    }
}