<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Setting\Repositories;


use MagicalConnection\Modules\Setting\Model\SettingModel;
use MagicalConnection\Support\Json;

/**
 * Provide repository operations for plugin settings.
 *
 * Handles persistence-level serialization and exposes settings as PHP arrays
 * to the service layer.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class SettingRepository
{

    private SettingModel $settingModel;

    /**
     * Create the setting repository.
     *
     * @param SettingModel $model Setting model.
     */
    public function __construct(SettingModel $model)
    {
        $this->settingModel = $model;
    }

    /**
     * Retrieve a setting group by key.
     *
     * The stored JSON value is decoded before being returned to the caller.
     *
     * @param string $key Setting key.
     *
     * @return array Setting value or an empty array when not found.
     */
    public function get(string $key): array
    {
        $setting = $this->settingModel->find($key);
        if (!$setting) {
            return [];
        }

        return Json::decode($setting['setting_value']) ?: [];
    }

    /**
     * Create a new setting group.
     *
     * @param string              $key   Setting key.
     * @param array $value Setting value.
     *
     * @return bool True when the setting was created successfully.
     */
    public function create(string $key, array $value): bool
    {
        return $this->settingModel->insert($key, Json::encode($value));
    }

    /**
     * Save an existing setting group.
     *
     * @param string              $key   Setting key.
     * @param array $value Setting value.
     *
     * @return bool True when the setting was updated successfully.
     */
    public function save(string $key, array $value): bool
    {
        return $this->settingModel->update($key, Json::encode($value));
    }


    /**
     * Retrieve all persisted settings.
     *
     * @return array Setting records.
     */
    public function getAll()
    {
        $result = $this->settingModel->all();
        if (is_null($result)) {
            return [];
        }
        return $result;
    }

}