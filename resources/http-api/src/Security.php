<?php

declare(strict_types=1);

namespace MagicalConnection\HttpAPI;
class Security
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function authenticate(): void
    {
        $providedKey = $this->getProvidedKey();
        if ($providedKey === null || !hash_equals($this->apiKey, $providedKey)) {
            Response::error('Invalid API key.', 401, 'auth.invalid_key');
        }
    }

    private function getProvidedKey(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!$header && function_exists('getallheaders')) {
            $headers = getallheaders();

            $header = $headers['Authorization']
                ?? $headers['authorization']
                ?? '';
        }

        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}