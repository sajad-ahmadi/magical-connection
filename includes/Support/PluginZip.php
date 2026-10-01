<?php

namespace MagicalConnection\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Creates downloadable ZIP archives for plugin resources.
 *
 * WHAT:
 * Creates a ZIP archive from a plugin resource directory.
 *
 * WHEN:
 * Used when a resource package needs to be downloaded.
 *
 * CONTRACT:
 * Returns the public URL of the generated ZIP archive.
 *
 * SIDE EFFECTS:
 * Creates a ZIP archive inside the WordPress uploads directory.
 *
 * @package CodeArt
 * @since 1.0.0
 */
final class PluginZip
{
    /**
     * Create a ZIP archive of the HTTP API package.
     *
     * @since 1.0.0
     *
     * @return string Download URL of the generated ZIP archive.
     */
    public static function createHttpApi(): string
    {
        $v = "1.0.0";
        $source = MAGICAL_CONNECTION_PATH . 'resources/http-api';

        if (!is_dir($source)) {
            throw new MagicalConnectionException(
                'The HTTP API directory does not exist.',
                'plugin_zip_source_not_found'
            );
        }

        $upload = wp_upload_dir();

        if (!empty($upload['error'])) {
            throw new MagicalConnectionException(
                'Unable to resolve the WordPress uploads directory.',
                'plugin_zip_upload_directory_error'
            );
        }

        $filename = 'magical-connection-http-api-'.$v.'.zip';

        $zipPath = trailingslashit($upload['basedir']) . $filename;
        $zipUrl = trailingslashit($upload['baseurl']) . $filename;

        /*
         * Reuse the existing archive when it has already been created.
         */
        if (file_exists($zipPath)) {
            return $zipUrl;
        }

        $zip = new ZipArchive();

        if (
            $zip->open(
                $zipPath,
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            ) !== true
        ) {
            throw new MagicalConnectionException(
                'Unable to create the HTTP API ZIP archive.',
                'plugin_zip_create_error'
            );
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $source,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();

            if ($filePath === false) {
                continue;
            }

            $relativePath = substr(
                $filePath,
                strlen($source)
            );

            $relativePath = ltrim(
                str_replace('\\', '/', $relativePath),
                '/'
            );

            $zip->addFile(
                $filePath,
                'http-api/' . $relativePath
            );
        }

        if (!$zip->close()) {
            throw new MagicalConnectionException(
                'Unable to finalize the HTTP API ZIP archive.',
                'plugin_zip_close_error'
            );
        }

        return $zipUrl;
    }
}
