<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Scanner\Repositories;

use MagicalConnection\Modules\Scanner\Model\ScanModel;
use MagicalConnection\Support\MagicalConnectionException;

/**
 * Provide repository operations for scanner records.
 *
 * Coordinates persistence and retrieval of scanner session records
 * while keeping database model details outside the scanner services.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class ScanRepository
{
    private ScanModel $model;

    /**
     * Create the scan repository.
     *
     * @param ScanModel $model Scan model.
     *
     * @since 1.0.0
     */
    public function __construct(ScanModel $model)
    {
        $this->model = $model;
    }

    /**
     * Create a scan record.
     *
     * @param array $data Scan data.
     *
     * @return int Inserted scan ID.
     *
     * @since 1.0.0
     */
    public function create(array $data)
    {
        $result = $this->model->insert($data);
        if (!$result) {
            throw new MagicalConnectionException(
                __('Unable to create scan.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'scanner.scan.create_failed'
            );
        }

        return $result;
    }

    /**
     * Update a scan record.
     *
     * @param int                 $scanId Scan record ID.
     * @param array $data   Fields to update.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function update(int $scanId, array $data): void
    {
        if (!$this->model->updateById($scanId, $data)) {
            throw new MagicalConnectionException(
                __('Unable to update scan.', MAGICAL_CONNECTION_TEXT_DOMAIN),
                'scanner.scan.update_failed'
            );
        }
    }

    /**
     * Find a scan by its database ID.
     *
     * @param int $scanId Scan record ID.
     *
     * @return array|null Scan record or null when not found.
     *
     * @since 1.0.0
     */
    public function find(int $scanId): ?array
    {
        return $this->model->findById($scanId);
    }

    /**
     * Find a scan by its UUID.
     *
     * @param string $uuid Scan UUID.
     *
     * @return array|null Scan record or null when not found.
     *
     * @since 1.0.0
     */
    public function findByUuid(string $uuid): ?array
    {
        return $this->model->findByUuid($uuid);
    }

    /**
     * Find the latest scan.
     *
     * @return array|null Latest scan or null when none exists.
     *
     * @since 1.0.0
     */
    public function latest(): ?array
    {
        return $this->model->findLatest();
    }

    /**
     * Delete a scan record by ID.
     *
     * @param int $scanId Scan record ID.
     *
     * @return bool Whether the record was deleted.
     *
     * @since 1.0.0
     */
    public function delete(int $scanId): bool
    {
        return $this->model->delete($scanId);
    }

}