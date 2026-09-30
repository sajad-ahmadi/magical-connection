<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Service;

use DOMDocument;
use DOMElement;

/**
 * Replace local media URLs with their remote transfer URLs in content.
 *
 * Resolves WordPress attachment IDs from image elements, identifies the
 * corresponding original or generated media size, and delegates URL
 * resolution to the media URL service.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaContentService
{

    /**
     * Cached attachment metadata indexed by attachment ID.
     *
     * @var array
     */
    private array $metadataMap = [];

    /**
     * Cached media filenames mapped to their WordPress size names.
     *
     * @var array
     */
    private array $sizeMap = [];

    private MediaUrlService $mediaUrlService;

    /**
     * Create the media content service.
     *
     * @param MediaUrlService $mediaUrlService Service used to resolve remote media URLs.
     *
     * @since 1.0.0
     */
    public function __construct(MediaUrlService $mediaUrlService)
    {
        $this->mediaUrlService = $mediaUrlService;
    }

    /**
     * Replace image URLs in HTML content with their remote URLs.
     *
     * Only images that can be associated with a WordPress attachment ID
     * are processed. Invalid or non-HTML content is returned unchanged.
     *
     * @param string $content Content containing HTML media elements.
     *
     * @return string Content with resolvable media URLs replaced.
     *
     * @since 1.0.0
     */
    public function replaceMediaUrls(string $content): string
    {
        if ($content === '') {
            return $content;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $html = '<?xml encoding="UTF-8">' . $content;
        if (!$dom->loadHTML($html)) {
            libxml_clear_errors();
            return $content;
        }

        libxml_clear_errors();
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $image) {
            if (!$image instanceof DOMElement) {
                continue;
            }
            $this->replaceImage($image);
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return $content;
        }

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    /**
     * Replace the supported media URLs of an image element.
     *
     * Processes both the primary `src` attribute and responsive `srcset`
     * candidates when they are present.
     *
     * @param DOMElement $image Image element to process.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function replaceImage(DOMElement $image): void
    {
        $attachmentId = $this->resolveAttachmentId($image);
        if ($attachmentId <= 0) {
            return;
        }

        $src = $image->getAttribute('src');
        if ($src !== '') {
            $image->setAttribute(
                'src',
                $this->resolveImageUrl(
                    $attachmentId,
                    $src
                )
            );
        }

        $srcset = $image->getAttribute('srcset');
        if ($srcset !== '') {
            $image->setAttribute(
                'srcset',
                $this->replaceSrcset(
                    $attachmentId,
                    $srcset
                )
            );
        }
    }

    /**
     * Resolve the WordPress attachment ID associated with an image.
     *
     * The `data-id` attribute is preferred, followed by the standard
     * WordPress `wp-image-{id}` CSS class.
     *
     * @param DOMElement $image Image element to inspect.
     *
     * @return int Attachment ID, or zero when no valid ID can be resolved.
     *
     * @since 1.0.0
     */
    private function resolveAttachmentId(DOMElement $image): int
    {

        $dataId = trim($image->getAttribute('data-id'));
        if ($dataId !== '') {
            $attachmentId = absint($dataId);
            if ($attachmentId > 0) {
                return $attachmentId;
            }
        }

        $class = $image->getAttribute('class');
        if ($class !== '') {
            if (preg_match('/(?:^|\s)wp-image-(\d+)(?:\s|$)/', $class, $matches)) {
                return absint($matches[1]);
            }
        }

        return 0;
    }

    /**
     * Resolve a media URL according to the attachment's media size.
     *
     * Original files are resolved through the general media URL service,
     * while generated WordPress image sizes are resolved through the
     * corresponding image-size lookup.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param string $src Current media URL.
     *
     * @return string Resolved media URL, or the original URL when it cannot be resolved.
     *
     * @since 1.0.0
     */
    private function resolveImageUrl(int $attachmentId, string $src): string
    {
        $filename = $this->getFilename($src);

        if ($filename === '') {
            return $src;
        }

        $sizeName = $this->getMediaSize($attachmentId, $filename);

        if ($sizeName === null) {
            return $src;
        }

        if ($sizeName === 'original') {
            return $this->mediaUrlService->getUrl($attachmentId, $src);
        }

        $result = $this->mediaUrlService->getImageSize($attachmentId, $sizeName);

        if ($result === null) {
            return $src;
        }

        return $result[0];
    }

    /**
     * Resolve the WordPress media size associated with a filename.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param string $filename Media filename.
     *
     * @return string|null Media size name, or null when the filename is unknown.
     *
     * @since 1.0.0
     */
    private function getMediaSize(int $attachmentId, string $filename): ?string
    {
        $this->loadMetadata($attachmentId);
        return $this->sizeMap[$attachmentId][$filename] ?? null;
    }

    /**
     * Load and cache WordPress attachment metadata.
     *
     * Builds a filename-to-size map for the original attachment file and
     * all generated WordPress image sizes.
     *
     * @param int $attachmentId WordPress attachment ID.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function loadMetadata(int $attachmentId): void
    {
        if (array_key_exists($attachmentId, $this->metadataMap)) {
            return;
        }

        $metadata = wp_get_attachment_metadata($attachmentId);
        $this->metadataMap[$attachmentId] = is_array($metadata) ? $metadata : [];
        $this->sizeMap[$attachmentId] = [];

        if (!empty($metadata['file']) && is_string($metadata['file'])) {
            $originalFilename = wp_basename($metadata['file']);
            $this->sizeMap[$attachmentId][$originalFilename] = 'original';
        }


        foreach (($metadata['sizes'] ?? []) as $sizeName => $size) {
            if (empty($size['file']) || !is_string($size['file'])) {
                continue;
            }
            $filename = wp_basename($size['file']);
            $this->sizeMap[$attachmentId][$filename] = (string)$sizeName;
        }
    }

    /**
     * Replace URLs in an image srcset attribute.
     *
     * Each srcset candidate is resolved independently while preserving
     * its responsive descriptor such as width or pixel density.
     *
     * @param int $attachmentId WordPress attachment ID.
     * @param string $srcset Image srcset attribute value.
     *
     * @return string Resolved srcset value.
     *
     * @since 1.0.0
     */
    private function replaceSrcset(int $attachmentId, string $srcset): string
    {
        $parts = preg_split('/\s*,\s*/', trim($srcset));
        if (!$parts) {
            return $srcset;
        }

        $result = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $segments = preg_split('/\s+/', $part, 2);
            $url = $segments[0] ?? '';
            $descriptor = $segments[1] ?? '';
            if ($url === '') {
                continue;
            }

            $newUrl = $this->resolveImageUrl($attachmentId, $url);
            $result[] = trim($newUrl . ' ' . $descriptor);
        }

        return implode(', ', $result);
    }

    /**
     * Extract the filename component from a media URL.
     *
     * @param string $url Media URL.
     *
     * @return string Filename, or an empty string when the URL has no valid path.
     *
     * @since 1.0.0
     */
    private function getFilename(string $url): string
    {
        $path = wp_parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) {
            return '';
        }
        return wp_basename($path);
    }
}