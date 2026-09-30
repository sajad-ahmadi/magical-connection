<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

use LogicException;

/**
 * Provide encryption and decryption utilities for sensitive data.
 *
 * Uses the WordPress authentication key as the cryptographic key
 * and AES-256-CBC for symmetric encryption.
 *
 * Encrypted values contain the initialization vector together with
 * the encrypted payload and are encoded using Base64 for storage
 * or transport.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class Crypto
{
    /**
     * Prevent instantiation of the utility class.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct()
    {
        throw new LogicException('Static class');
    }

    /**
     * Encrypt plain text using AES-256-CBC.
     *
     * Generates a random initialization vector for each encryption
     * operation and prefixes it to the encrypted payload before
     * encoding the result as Base64.
     *
     * @since 1.0.0
     *
     * @param string $plainText Plain text to encrypt.
     *
     * @return string Base64-encoded encrypted value.
     */
    public static function encrypt(string $plainText): string
    {

        $cipher = 'AES-256-CBC';

        $key = hash('sha256', AUTH_KEY, true);
        $iv = random_bytes(openssl_cipher_iv_length($cipher));
        $encrypted = openssl_encrypt(
            $plainText,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a Base64-encoded AES-256-CBC value.
     *
     * Extracts the initialization vector from the beginning of the
     * decoded payload and uses it to decrypt the remaining encrypted
     * data.
     *
     * @since 1.0.0
     *
     * @param string $cipherText Base64-encoded encrypted value.
     *
     * @return string Decrypted plain text.
     */
    public static function decrypt(string $cipherText): string
    {

        $cipher = 'AES-256-CBC';

        $key = hash('sha256', AUTH_KEY, true);
        $data = base64_decode($cipherText);
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt(
            $encrypted,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

}