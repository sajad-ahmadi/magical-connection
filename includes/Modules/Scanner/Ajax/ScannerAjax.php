<?php
declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Ajax;


use MagicalConnection\Modules\Scanner\Repositories\ScanFileRepository;
use MagicalConnection\Modules\Scanner\Services\MediaScannerService;
use MagicalConnection\Support\AjaxNonce;
use MagicalConnection\Support\MagicalConnectionException;
/**
 * Handle AJAX requests for media scanner operations.
 *
 * Provides endpoints for starting scans, running real-time scans,
 * processing paginated scan batches, and retrieving scan statistics.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ScannerAjax
{

    private MediaScannerService $scanner;

    private ScanFileRepository $scanFileRepository;

    /**
     * Create the scanner AJAX handler.
     *
     * @param MediaScannerService $scanner           Media scanner service.
     * @param ScanFileRepository  $scanFileRepository Scan file repository.
     *
     * @since 1.0.0
     */
    public function __construct(MediaScannerService $scanner, ScanFileRepository $scanFileRepository)
    {
        $this->scanner = $scanner;
        $this->scanFileRepository = $scanFileRepository;
    }

    /**
     * Register scanner AJAX endpoints.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function register(): void
    {
        add_action('wp_ajax_magical_scanner_start', [$this, 'start']);
        add_action('wp_ajax_magical_scanner_real_time', [$this, 'realTime']);
        add_action('wp_ajax_magical_scanner_batch', [$this, 'batch']);
        add_action('wp_ajax_magical_scanner_data', [$this, 'getData']);
    }

    /**
     * Run a real-time scanner operation and stream its progress.
     *
     * Starts an SSE response, enables active scanning, executes the scan,
     * and sends the final result through the stream.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function realTime(): void
    {
        AjaxNonce::verifyOrFail();
        $stream = mc_stream();
        try {

            $stream->start();
            $scanId = "";
            if (isset($_POST['uuid'])) {
                $scanId = sanitize_text_field($_POST['uuid']);
            }else{
                $scanId = wp_generate_uuid4();
            }
            $stream->log("Start Scanning", [
                'event_method' => 'scan',
                'status'       => 'started',
                'uuid'         => $scanId,
            ]);

            $this->scanner->realActiveScan(true);
            $result = $this->scanner->scan($scanId);

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
        }
    }

    /**
     * Return scan statistics through AJAX.
     *
     * Provides summary statistics grouped by file type and extension.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function getData(): void
    {
        AjaxNonce::verifyOrFail();
        try {
            $summaryByType = $this->scanFileRepository->summaryByType();
            $statisticsByExtension = $this->scanFileRepository->statisticsByExtension();

            wp_send_json_success([
                'summary' => $summaryByType,
                'extension' => $statisticsByExtension['extension'],
                'extension_total' => $statisticsByExtension['extension_total'],
            ]);

        } catch (MagicalConnectionException $exception) {
            $this->error($exception);
        }
    }

    /**
     * Start a scanner operation through AJAX.
     *
     * Creates a new scan UUID when the request includes the uuid
     * parameter and returns the scanner result as JSON.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function start(): void
    {
        AjaxNonce::verifyOrFail();
        try {
            $scanId = "";
            if (isset($_POST['uuid'])) {
                $scanId = sanitize_text_field($_POST['uuid']);
            }else{
                $scanId = wp_generate_uuid4();
            }
            $result = $this->scanner->scan($scanId);
            wp_send_json_success($result);

        } catch (MagicalConnectionException $exception) {
            $this->error($exception);
        }
    }

    /**
     * Process a paginated scanner batch.
     *
     * Uses a default batch size of 100 files and increases it to 500
     * when the scan booster option is enabled.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function batch(): void
    {
        AjaxNonce::verifyOrFail();
        try {
            $scanId = sanitize_text_field(wp_unslash($_POST['scan_id'] ?? ''));
            $page = absint($_POST['page'] ?? 0);
            $perPage = 100;

            if (isset($_POST['scan_booster']) && filter_var($_POST['scan_booster'], FILTER_VALIDATE_BOOLEAN)) {
                $perPage = 500;
            }

            if ($scanId === '') {
                throw new MagicalConnectionException(
                    'Scan ID is required.',
                    'scanner.scan_id.required'
                );
            }

            if ($page < 1) {
                throw new MagicalConnectionException(
                    'Invalid scan page.',
                    'scanner.page.invalid'
                );
            }

            $result = $this->scanner->scan($scanId, $page, $perPage);
            wp_send_json_success($result);

        } catch (MagicalConnectionException $exception) {
            $this->error($exception);
        }
    }

    /**
     * Send a standardized scanner AJAX error response.
     *
     * @param MagicalConnectionException $exception Scanner exception.
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function error(MagicalConnectionException $exception): void
    {
        wp_send_json_error(
            [
                'message' => $exception->getMessage(),
            ],
            422
        );
    }
}