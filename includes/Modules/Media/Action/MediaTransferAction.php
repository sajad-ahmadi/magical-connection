<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Action;

use MagicalConnection\Modules\Media\Repositories\MediaTransferRepository;
use MagicalConnection\Modules\Media\Service\MediaContentService;
use MagicalConnection\Modules\Media\Service\MediaReferenceService;
use MagicalConnection\Modules\Media\Service\MediaUrlService;
use MagicalConnection\Modules\Scanner\Services\MediaScannerService;
use WP_Post;

/**
 * Register and handle media transfer and media URL actions.
 *
 * Provides media library transfer controls, registers media scanning hooks,
 * and replaces attachment URLs and media references with their configured
 * remote representations when required.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaTransferAction
{

    /**
     * Media URL service.
     *
     * @var MediaUrlService
     *
     * @since 1.0.0
     */
    private MediaUrlService $mediaUrlService;

    /**
     * Media reference service.
     *
     * @var MediaReferenceService
     *
     * @since 1.0.0
     */
    private MediaReferenceService $mediaReferenceService;

    /**
     * Media content service.
     *
     * @var MediaContentService
     *
     * @since 1.0.0
     */
    private MediaContentService $mediaContentService;

    /**
     * Repository for media transfer records.
     *
     * @var MediaTransferRepository
     *
     * @since 1.0.0
     */
    private MediaTransferRepository $mediaTransferRepository;

    /**
     * Media scanner service.
     *
     * @var MediaScannerService
     *
     * @since 1.0.0
     */
    private MediaScannerService $mediaScannerService;

    /**
     * Media library column identifier.
     *
     * @since 1.0.0
     */
    private const COLUMN_KEY = 'magical_connection_transfer';

    /**
     * Create the media transfer action handler.
     *
     * @param MediaUrlService         $mediaUrlService         Media URL management service.
     * @param MediaReferenceService   $mediaReferenceService   Media reference service.
     * @param MediaContentService     $mediaContentService     Media content replacement service.
     * @param MediaTransferRepository $mediaTransferRepository Media transfer repository.
     * @param MediaScannerService     $mediaScannerService    Media scanner service.
     *
     * @since 1.0.0
     */
    public function __construct(
        MediaUrlService         $mediaUrlService,
        MediaReferenceService   $mediaReferenceService,
        MediaContentService     $mediaContentService,
        MediaTransferRepository $mediaTransferRepository,
        MediaScannerService     $mediaScannerService
    )
    {
        $this->mediaUrlService = $mediaUrlService;
        $this->mediaReferenceService = $mediaReferenceService;
        $this->mediaContentService = $mediaContentService;
        $this->mediaTransferRepository = $mediaTransferRepository;
        $this->mediaScannerService = $mediaScannerService;
    }

    /**
     * Register media-related WordPress hooks.
     *
     * Registers admin media-library controls, media scanning hooks,
     * frontend media URL replacement, and the transfer dialog.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        add_filter('manage_media_columns', [$this, 'addColumn']);
        add_action('manage_media_custom_column', [$this, 'renderColumn'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_footer-upload.php', [$this, 'renderModal']);

        add_action('add_attachment', [$this, 'addAttachment']);

        add_action('template_redirect', [$this, 'prepareCurrentContent']);
        add_filter('wp_get_attachment_url', [$this, 'replaceAttachmentUrl'], 10, 2);
        add_filter('image_downsize', [$this, 'replaceImageSize'], 10, 3);
        add_filter('the_content', [$this, 'replaceContentMediaUrls'], 20);

    }

    /**
     * Add the Magical Connection column to the media library.
     *
     * @param array $columns Existing media library columns.
     *
     * @return array Updated media library columns.
     *
     * @since 1.0.0
     */
    public function addColumn(array $columns): array
    {
        $columns[self::COLUMN_KEY] = 'Magical Connection';
        return $columns;
    }

    /**
     * Render the transfer action for a media library item.
     *
     * The available action depends on whether the attachment already has
     * a completed transfer record.
     *
     * @param string $columnName  Current media library column identifier.
     * @param int    $attachmentId Attachment post ID.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function renderColumn(string $columnName, int $attachmentId): void
    {

        if ($columnName !== self::COLUMN_KEY) {
            return;
        }

        $post = get_post($attachmentId);

        if (!$post instanceof WP_Post) {
            return;
        }
        $transferred = $this->mediaTransferRepository->existsTransferred($attachmentId);

        $label = $transferred ? esc_html__( 'Transfer to WordPress' , MAGICAL_CONNECTION_TEXT_DOMAIN ) : esc_html__('Transfer to Remote Server' , MAGICAL_CONNECTION_TEXT_DOMAIN);
        $action = $transferred ? 'restore' : 'transfer';


        printf(
            '<button
            type="button"
            class="button button-primary magical-transfer-media"
            data-attachment-id="%d"
            data-action="%s">%s</button>',
            $attachmentId,
            esc_attr($action),
            $label
        );
    }

    /**
     * Enqueue media transfer assets in the WordPress admin.
     *
     * Provides the media transfer script with the AJAX endpoint and
     * the nonce required for authenticated requests.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function enqueue(): void
    {

        wp_enqueue_script(
            'magical-connection-media-transfer',
            MAGICAL_CONNECTION_URL . 'assets/js/media-transfer.js',
            [],
            MAGICAL_CONNECTION_VERSION,
            true
        );

        wp_localize_script(
            'magical-connection-media-transfer',
            'MagicalMedia',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),

                'nonce' => wp_create_nonce(
                    'magical_media_transfer'
                ),
            ]
        );
    }

    /**
     * Add a newly created attachment to the media scan workflow.
     *
     * @param int $attachment_id Newly created attachment ID.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addAttachment(int $attachment_id): void
    {
        $this->mediaScannerService->addFileToScan($attachment_id);
    }

    /**
     * Render the media transfer dialog in the media library.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function renderModal(): void
    {
        mc_view()->render('dialogs.transfer.index');
    }

    /**
     * Prepare media references for the current singular content.
     *
     * Collects attachment references associated with the current content
     * and prepares their URLs for frontend rendering.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function prepareCurrentContent(): void
    {
        if (is_admin() || !is_singular()) {
            return;
        }

        $postId = get_queried_object_id();
        if ($postId <= 0) {
            return;
        }

        $attachmentIds = $this->mediaReferenceService->getAttachmentIds($postId);

        if (empty($attachmentIds)) {
            return;
        }

        $this->mediaUrlService->prepare($attachmentIds);
    }

    /**
     * Replace an attachment URL when a remote representation is available.
     *
     * @param string $url          Original attachment URL.
     * @param int    $attachmentId Attachment ID.
     *
     * @return string Resolved attachment URL.
     *
     * @since 1.0.0
     */
    public function replaceAttachmentUrl(string $url, int $attachmentId): string
    {
        return $this->mediaUrlService->getUrl(
            $attachmentId,
            $url
        );
    }

    /**
     * Replace an image size result when a remote representation is available.
     *
     * @param mixed       $downsize      Original image downsize result.
     * @param int         $attachmentId  Attachment ID.
     * @param string|int[]|array $size   Requested image size.
     *
     * @return mixed Resolved image size result or the original value.
     *
     * @since 1.0.0
     */
    public function replaceImageSize($downsize, int $attachmentId, $size)
    {
        $result = $this->mediaUrlService->getImageSize($attachmentId, $size);

        if ($result === null) {
            return $downsize;
        }

        return $result;
    }

    /**
     * Replace media URLs contained in post content.
     *
     * @param string $content Original post content.
     *
     * @return string Content with resolved media URLs.
     *
     * @since 1.0.0
     */
    public function replaceContentMediaUrls(string $content): string
    {
        return $this->mediaContentService->replaceMediaUrls($content);
    }
}