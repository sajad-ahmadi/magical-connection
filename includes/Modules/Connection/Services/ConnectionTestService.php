<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Connection\Services;

use MagicalConnection\Modules\Connection\DTO\ConnectionDTO;
use MagicalConnection\Support\MagicalConnectionException;
use Throwable;

/**
 * Test configured connections through connection, upload, and delete checks.
 *
 * Runs connection-level diagnostics and returns structured results suitable
 * for AJAX responses and connection management workflows.
 *
 * @since   1.0.0
 * @package CodeArt
 *
 */
class ConnectionTestService
{
    /**
     * Filename used for temporary connection test files.
     *
     * @since 1.0.0
     */
    private const TEST_FILENAME = 'magical-connection-test.txt';

    /**
     * Supported connection test steps.
     *
     * @since 1.0.0
     * @var array
     *
     */
    public const LIST_TEST_STEP = ['connection', 'upload', 'delete'];

    /**
     * Create connection clients for testing.
     *
     * @since 1.0.0
     * @var ConnectionFactory
     *
     */
    private ConnectionFactory $connectionFactory;

    /**
     * Create the connection test service.
     *
     * @since 1.0.0
     * @param ConnectionFactory $connectionFactory Connection client factory.
     *
     */
    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * Run a single connection test step.
     *
     * @param ConnectionDTO $connection Connection configuration to test.
     * @param string         $step       Test step identifier.
     *
     * @return array Structured test result.
     *
     * @since 1.0.0
     */
    public function testStep(ConnectionDTO $connection, string $step): array
    {
        switch ($step) {
            case 'connection':
                return $this->testConnection($connection);
            case 'upload':
                return $this->testUpload($connection);
            case 'delete':
                return $this->testDelete($connection);
            default:
                throw new MagicalConnectionException(
                    __('Invalid test step. Allowed steps: connection, upload, delete.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                    'invalid_test_step'
                );
        }
    }

    /**
     * Run all connection tests in sequence.
     *
     * The upload and delete tests are executed only when the previous step
     * succeeds. The first failed step stops the test sequence.
     *
     * @param ConnectionDTO $connection Connection configuration to test.
     *
     * @return array Complete test result with step details.
     *
     * @since 1.0.0
     */
    public function testFull(ConnectionDTO $connection): array
    {

        $result = [
            'success' => false,
            'steps'   => [
                'connection' => null,
                'upload'     => [
                    'success' => false,
                ],
                'delete'     => [
                    'success' => false,
                ],
            ],
        ];

        $result['steps']['connection'] = $this->testConnection($connection);
        if (!$result['steps']['connection']['success']) {
            return $result;
        }

        $result['steps']['upload'] = $this->testUpload($connection);
        if (!$result['steps']['upload']['success']) {
            return $result;
        }

        $result['steps']['delete'] = $this->testDelete($connection);
        if (!$result['steps']['delete']['success']) {
            return $result;
        }

        $result['success'] = true;
        return $result;
    }

    /**
     * Test whether the configured connection can be established.
     *
     * The client is disconnected after the test regardless of its result.
     *
     * @param ConnectionDTO $connection Connection configuration to test.
     *
     * @return array Connection test result.
     *
     * @since 1.0.0
     */
    private function testConnection(ConnectionDTO $connection): array
    {
        $client = $this->connectionFactory->create($connection);

        try {
            if (!$client->connect()) {
                throw new MagicalConnectionException(
                    'Unable to connect to the server.',
                    'connection_test_failed'
                );
            }

            return [
                'success' => true,
                'step'    => 'connection',
                'message' => 'Connection successful.',
            ];
        } catch (MagicalConnectionException $e) {
            return $this->failure($e, 'connection_test_failed');
        } finally {
            if ($client->isConnected()) {
                $client->disconnect();
            }
        }
    }

    /**
     * Test whether a file can be uploaded to the remote connection.
     *
     * Creates a temporary local test file, uploads it to the configured
     * remote test path, and removes the local file after the test.
     *
     * @param ConnectionDTO $connection Connection configuration to test.
     *
     * @return array Upload test result.
     *
     * @since 1.0.0
     */
    private function testUpload(ConnectionDTO $connection): array
    {
        $client = $this->connectionFactory->create($connection);
        $localPath = null;

        try {
            if (!$client->connect()) {
                throw new MagicalConnectionException(
                    'Unable to connect to the server.',
                    'connection_test_failed'
                );
            }

            $localPath = $this->createTestFile();
            $remotePath = $this->getRemoteTestPath();

            $client->file()->upload($localPath, $remotePath);


            return [
                'success'     => true,
                'step'        => 'upload',
                'message'     => 'File uploaded successfully.',
                'remote_path' => $remotePath,
            ];

        } catch (Throwable $e) {
            return $this->failure($e, 'upload_test_failed');
        } finally {

            if ($localPath !== null && is_file($localPath)) {
                @unlink($localPath);
            }

            if ($client->isConnected()) {
                $client->disconnect();
            }
        }
    }

    /**
     * Test whether a remote test file can be uploaded and deleted.
     *
     * Creates a temporary local test file, uploads it to the configured
     * remote test path, deletes the remote file, and removes the local file
     * after the test.
     *
     * @param ConnectionDTO $connection Connection configuration to test.
     *
     * @return array Delete test result.
     *
     * @since 1.0.0
     */
    private function testDelete(ConnectionDTO $connection): array
    {
        $client = $this->connectionFactory->create($connection);
        $localPath = null;

        try {

            if (!$client->connect()) {
                throw new MagicalConnectionException(
                    'Unable to connect to the server.',
                    'connection_test_failed'
                );
            }

            $localPath = $this->createTestFile();
            $remotePath = $this->getRemoteTestPath();

            $client->file()->upload($localPath, $remotePath);
            $client->file()->delete($remotePath);

            return [
                'success'     => true,
                'step'        => 'delete',
                'message'     => 'File deleted successfully.',
                'remote_path' => $remotePath,
            ];

        } catch (Throwable $e) {
            return $this->failure($e, 'delete_test_failed');
        } finally {

            if ($localPath !== null && is_file($localPath)) {
                @unlink($localPath);
            }
            if ($client->isConnected()) {
                $client->disconnect();
            }
        }
    }

    /**
     * Create the local file used by upload and delete tests.
     *
     * The test file is stored inside the WordPress uploads directory so the
     * test does not depend on a plugin-specific writable location.
     *
     * @return string Absolute path to the created test file.
     *
     * @since 1.0.0
     */
    private function createTestFile(): string
    {
        $uploadDir = wp_upload_dir();

        if (!empty($uploadDir['error'])) {
            throw new MagicalConnectionException(
                $uploadDir['error'],
                'upload_directory_error'
            );
        }


        $testDirectory = trailingslashit($uploadDir['basedir']) . 'magical-connection/tests';

        if (!is_dir($testDirectory)) {
            if (!wp_mkdir_p($testDirectory)) {
                throw new MagicalConnectionException(
                    'Unable to create test directory.',
                    'test_directory_create_failed'
                );
            }
        }

        $path = trailingslashit($testDirectory) . self::TEST_FILENAME;

        if (file_put_contents($path, 'Magical Connection Test') === false) {
            throw new MagicalConnectionException(
                'Unable to create test file.',
                'test_file_create_failed'
            );
        }

        return $path;
    }

    /**
     * Get the remote path used by connection tests.
     *
     * @return string Remote test file path.
     *
     * @since 1.0.0
     */
    private function getRemoteTestPath(): string
    {
        return self::TEST_FILENAME;
    }

    /**
     * Convert an exception into a normalized test failure result.
     *
     * Uses the application error key when the exception is a
     * MagicalConnectionException and falls back to the supplied code for
     * unexpected errors.
     *
     * @param Throwable $e           Exception or error raised during testing.
     * @param string    $defaultCode Fallback application error code.
     *
     * @return array Normalized failure result.
     *
     * @since 1.0.0
     */
    private function failure(Throwable $e, string $defaultCode): array
    {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code'    => $e instanceof MagicalConnectionException ? $e->getErrorKey() : $defaultCode,
        ];
    }
}