<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Media\Ajax;

use MagicalConnection\Modules\Media\Service\MediaReferenceService;
use MagicalConnection\Modules\Media\Service\MediaTransferService;
use MagicalConnection\Support\AjaxNonce;
use MagicalConnection\Support\MagicalConnectionException;
use Throwable;

/**
 * Handle AJAX requests for media transfer operations.
 *
 * Provides endpoints for retrieving media information, transferring media,
 * synchronizing media references, and restoring transferred media.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class MediaTransferAjax
{
    /**
     * Handle media transfer operations.
     *
     * @var MediaTransferService
     *
     * @since 1.0.0
     */
    private MediaTransferService $service;

    /**
     * Manage media attachment references.
     *
     * @var MediaReferenceService
     *
     * @since 1.0.0
     */
    private MediaReferenceService $referenceService;

    /**
     * Create the media transfer AJAX handler.
     *
     * @param MediaTransferService $service         Media transfer service.
     * @param MediaReferenceService $referenceService Media reference service.
     *
     * @since 1.0.0
     */
    public function __construct(MediaTransferService $service, MediaReferenceService $referenceService)
    {
        $this->service = $service;
        $this->referenceService = $referenceService;
    }

    /**
     * Register media transfer AJAX actions.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        add_action('wp_ajax_magical_media_info', [$this, 'info']);
        add_action('wp_ajax_magical_media_transfer', [$this, 'transfer']);
        add_action('wp_ajax_magical_media_refernce', [$this, 'reference']);
        add_action('wp_ajax_magical_media_restore', [$this, 'restore']);
    }

    /**
     * Return information about a media attachment.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function info(): void
    {
        AjaxNonce::verifyOrFail();

        $attachmentId = absint($_POST['attachment_id'] ?? 0);

        if ($attachmentId <= 0) {
            wp_send_json_error(
                [
                    'message' => __('Invalid attachment ID.', MAGICAL_CONNECTION_TEXT_DOMAIN)
                ],
                400
            );
        }

        try {
            $data = $this->service->getInfo($attachmentId);
            wp_send_json_success($data);
        } catch (Throwable $e) {
            wp_send_json_error(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }

    /**
     * Restore a media attachment from the remote server.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function restore(): void
    {
        AjaxNonce::verifyOrFail();

        $attachmentId = absint($_POST['attachment_id'] ?? 0);

        if ($attachmentId <= 0) {
            wp_send_json_error(
                [
                    'message' => __('Invalid attachment ID.', MAGICAL_CONNECTION_TEXT_DOMAIN)
                ],
                400
            );
        }

        try {
            $data = $this->service->restore($attachmentId);
            wp_send_json_success([
                'restored' => $data
            ]);
        } catch (Throwable $e) {
            wp_send_json_error(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }

    /**
     * Synchronize references associated with a media attachment.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function reference(): void
    {
        AjaxNonce::verifyOrFail();

        $attachmentId = absint($_POST['attachment_id'] ?? 0);

        if ($attachmentId <= 0) {
            wp_send_json_error(
                [
                    'message' => __('Invalid attachment ID.', MAGICAL_CONNECTION_TEXT_DOMAIN)
                ],
                400
            );
        }

        try {
            $data = $this->referenceService->syncAttachmentReferences($attachmentId);
            wp_send_json_success($data);
        } catch (Throwable $e) {
            wp_send_json_error(
                [
                    'message' => $e->getMessage()
                ],
                400
            );
        }
    }

    /**
     * Transfer a media attachment while streaming progress to the client.
     *
     * Starts an SSE response, reports transfer progress, and sends a final
     * success or error event when the transfer completes.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function transfer(): void
    {

        AjaxNonce::verifyOrFail();

        $stream = mc_stream();

        $attachment_id = absint($_POST['attachment_id'] ?? 0);
        $connection_id = absint($_POST['connection_id'] ?? 0);

        if ($attachment_id <= 0) {
            $stream->error(
                __('Invalid attachment ID.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'invalid_attachment_id'
            );
            $stream->finish();
        }

        if ($connection_id <= 0) {
            $stream->error(
                __('Invalid connection ID.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'invalid_connection_id'
            );
            $stream->finish();
        }

        try {

            $stream->start();
            $stream->log("Start transfer", [
                'event_method' => 'start'
            ]);

            $this->service->transfer($attachment_id, $connection_id);

            $stream->finish(
                [
                    'success' => true,
                    'message' => __('Scan completed successfully.', MAGICAL_CONNECTION_TEXT_DOMAIN)
                ]
            );

        } catch (MagicalConnectionException $exception) {

            $stream->error(
                $exception->getMessage(),
                $exception->getErrorKey()
            );
            $stream->finish();

        } catch (Throwable $exception) {
            echo $exception->getMessage();
            $stream->error(
                __('An unexpected error occurred.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'unknown_error'
            );
        }

        exit;
    }
}