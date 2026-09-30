<?php

declare(strict_types=1);

namespace MagicalConnection\HttpAPI;

class API
{
    private Security $security;

    private Upload $upload;

    private Delete $delete;

    public function __construct(Security $security, Upload $upload, Delete $delete)
    {
        $this->security = $security;
        $this->upload = $upload;
        $this->delete = $delete;
    }

    public function handle(): void
    {
        $this->security->authenticate();

        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        $action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';

        switch ($action) {
            case 'ping':
                $this->ping();
                break;
            case 'upload':
                $this->uploadFile($method);
                break;
            case 'delete':
                $this->deleteFile($method);
                break;
            default:
                Response::error('Unknown API action.', 404, 'api.unknown_action');
        }
    }

    private function ping(): void
    {
        Response::success([
            'service' => 'Magical Connection Api',
            'version' => '1.0.0',
        ]);
    }

    /**
     * Simple file upload.
     *
     * POST:
     * ?action=upload
     *
     * multipart/form-data:
     * file = uploaded file
     */
    private function uploadFile(string $method): void
    {
        $this->requireMethod($method, 'POST');
        if (!isset($_FILES['file'])) {
            Response::error('File is required.', 422, 'upload.file_required');
        }

        $file = $_FILES['file'];

        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            Response::error(
                $this->getUploadErrorMessage(isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE),
                422,
                'upload.file_failed'
            );
        }

        $remotePath = isset($_POST['remote_path']) ? trim((string)$_POST['remote_path']) : '';

        if ($remotePath === '') {
            Response::error('Remote path is required.', 422, 'upload.remote_path_required');
        }

        $result = $this->upload->store($file, $remotePath);
        Response::success($result);
    }

    private function getPostData(): array
    {
        if (empty($_POST)) {
            Response::error('POST data is empty.', 422, 'request.empty_post');
        }

        return $_POST;
    }

    private function requireMethod(string $actual, string $expected): void
    {
        if ($actual !== $expected) {
            Response::error('Method not allowed.', 405, 'http.method_not_allowed');
        }
    }

    private function getUploadErrorMessage(int $error): string
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the server upload limit.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the form upload limit.';
            case UPLOAD_ERR_PARTIAL:
                return 'The file was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary upload directory.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write uploaded file.';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload.';
            default:
                return 'Unknown file upload error.';
        }
    }

    private function deleteFile(string $method): void
    {
        $this->requireMethod($method, 'POST');
        $remotePath = isset($_POST['remote_path']) ? trim((string)$_POST['remote_path']) : '';

        if ($remotePath === '') {
            Response::error('Remote path is required.', 422, 'delete.remote_path_required');
        }

        $result = $this->delete->file($remotePath);
        Response::success($result);
    }
}