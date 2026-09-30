<?php

declare(strict_types=1);

namespace MagicalConnection\Modules\Setting\Services;

use MagicalConnection\Modules\Setting\DefaultSettings;
use MagicalConnection\Modules\Setting\Repositories\SettingRepository;

/**
 * Manage plugin settings and default configuration.
 *
 * Provides access to grouped settings, initializes missing default groups,
 * and persists setting changes through the settings repository.
 *
 * @package CodeArt
 *
 * @since 1.0.0
 */
class SettingManager
{
    private SettingRepository $repository;

    /**
     * Create the setting manager.
     *
     * @param SettingRepository $repository Settings repository.
     */
    public function __construct(SettingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Initialize default setting groups.
     *
     * Existing settings are merged with the current defaults so newly added
     * default keys become available without overwriting persisted values.
     *
     * @return void
     */
    public function boot(): void
    {
        foreach (DefaultSettings::all() as $group => $defaults) {

            $current = $this->repository->get($group);
            if (!is_array($current) || empty($current)) {
                $this->repository->create($group, $defaults);
                continue;
            }

            $settings = array_merge($defaults, $current);
            $this->repository->save($group, $settings);
        }
    }


    /**
     * Retrieve a setting or an entire setting group.
     *
     * Dot notation is used for individual values, for example
     * `cron.enabled`. Passing only a group key returns the complete group.
     *
     * @param string $key     Setting key or group key.
     * @param mixed  $default Default value when the requested setting does not exist.
     *
     * @return mixed Setting value, group array, or the supplied default.
     */
    public function get(string $key, $default = null)
    {

        [$group, $setting] = $this->parseKey($key);

        $settings = $this->repository->get($group);

        if ($setting === null) {
            return $settings ?? $default;
        }
        return $settings[$setting] ?? $default;
    }

    /**
     * Retrieve multiple settings or groups.
     *
     * Each requested key is resolved independently using the same rules as
     * {@see get()}.
     *
     * @param array $keys Setting keys.
     *
     * @return array Resolved settings indexed by requested key.
     */
    public function gets(array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * Set an individual setting value.
     *
     * The existing group is loaded, the requested key is replaced, and the
     * complete group is persisted through the repository.
     *
     * @param string $key   Setting key using group.setting notation.
     * @param mixed  $value Setting value.
     *
     * @return bool True when the setting group was saved successfully.
     */
    public function set(string $key, $value): bool
    {

        [$group, $setting] = $this->parseKey($key);
        $settings = $this->repository->get($group);
        if (!is_array($settings)) {
            $settings = [];
        }

        $settings[$setting] = $value;
        return $this->repository->save($group, $settings);
    }

    /**
     * Replace an entire setting group.
     *
     * @param string              $group    Setting group key.
     * @param array $settings Group settings.
     *
     * @return bool True when the group was saved successfully.
     */
    public function updateGroup(string $group, array $settings): bool
    {
        return $this->repository->save($group, $settings);
    }

    /**
     * Retrieve all persisted settings.
     *
     * @return array Persisted setting records.
     */
    public function getAllSettings(): array
    {
        return $this->repository->getAll();
    }

    /**
     * Determine whether a setting exists.
     *
     * A setting with a stored null value is considered present.
     *
     * @param string $key Setting key using group.setting notation.
     *
     * @return bool True when the setting key exists.
     */
    public function has(string $key): bool
    {
        [$group, $setting] = $this->parseKey($key);

        $settings = $this->repository->get($group);

        if (!is_array($settings)) {
            return false;
        }

        return array_key_exists($setting, $settings);
    }


    /**
     * Split a setting key into its group and setting components.
     *
     * Keys use the `group.setting` format. A key without a dot represents
     * the complete setting group.
     *
     * @param string $key Setting key.
     *
     * @return array Group and setting name.
     */
    private function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);

        return [$parts[0], $parts[1] ?? null,];
    }
}