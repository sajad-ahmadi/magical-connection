<?php

declare(strict_types=1);

namespace MagicalConnection\HttpAPI;
class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(array $data = []): void
    {
        self::json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public static function error(string $message, int $status = 400, string $code = 'api.error'): void
    {
        self::json([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $status);
    }
}