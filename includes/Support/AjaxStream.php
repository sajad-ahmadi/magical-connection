<?php
declare(strict_types=1);

namespace MagicalConnection\Support;

/**
 * Manage Server-Sent Events (SSE) responses for AJAX requests.
 *
 * Provides a lightweight interface for starting an SSE response,
 * sending log and error events, and signaling the completion of
 * a long-running AJAX operation.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class AjaxStream
{
    /**
     * Indicates whether the SSE stream has been started.
     *
     * @since 1.0.0
     */
    private bool $active = false;

    /**
     * Start the SSE response.
     *
     * Initializes the response headers, disables the execution time
     * limit, clears the active output buffer, and sends the initial
     * stream data to the client.
     *
     * Calling this method more than once has no effect.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->active) {
            return;
        }

        $this->active = true;

        if (ob_get_level()) {
            ob_end_clean();
        }

        set_time_limit(0);

        header('Content-Type: text/event-stream; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        $this->flush();
    }

    /**
     * Send a log event to the connected client.
     *
     * The message is sent as an SSE `log` event together with
     * optional additional data.
     *
     * @since 1.0.0
     *
     * @param string $message            Log message to send.
     * @param array $data Additional event data.
     *
     * @return void
     */
    public function log(string $message, array $data = []): void
    {
        if (!$this->active) {
            return;
        }

        $this->send('log', array_merge(
            [
                'message' => $message,
            ],
            $data
        ));
    }

    /**
     * Send an error event to the connected client.
     *
     * The event contains a human-readable message and an optional
     * application-specific error code.
     *
     * @since 1.0.0
     *
     * @param string $message Error message to send.
     * @param string $code    Optional application-specific error code.
     *
     * @return void
     */
    public function error(string $message, string $code = ''): void
    {
        if (!$this->active) {
            return;
        }

        $this->send('error', [
            'message' => $message,
            'code'    => $code,
        ]);
    }

    /**
     * Send the final event and close the stream.
     *
     * Marks the stream as inactive after the `finish` event has
     * been sent to the client.
     *
     * Calling this method on an inactive stream has no effect.
     *
     * @since 1.0.0
     *
     * @param array $data Data to include in the final event.
     *
     * @return void
     */
    public function finish(array $data = []): void
    {
        if (!$this->active) {
            return;
        }

        $this->send('finish', $data);

        $this->active = false;
    }

    /**
     * Send an SSE event to the connected client.
     *
     * Encodes the event payload as JSON and immediately flushes
     * the response so the client can process the event without
     * waiting for the request to complete.
     *
     * @since 1.0.0
     *
     * @param string $event              Event name.
     * @param array $data Event payload.
     *
     * @return void
     */
    private function send(string $event, array $data = []): void
    {
        if (!$this->active) {
            return;
        }

        echo "event: {$event}\n";
        echo 'data: ' . wp_json_encode($data) . "\n\n";

        $this->flush();
    }

    /**
     * Determine whether the SSE stream is currently active.
     *
     * @since 1.0.0
     *
     * @return bool True when the stream has been started and not finished.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Flush buffered output to the client.
     *
     * Ensures that SSE events are delivered immediately instead of
     * remaining in the PHP output buffer.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}