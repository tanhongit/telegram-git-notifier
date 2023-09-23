<?php

namespace LbilTech\TelegramGitNotifier\Models;

use LbilTech\TelegramGitNotifier\Constants\SettingConstant;

class Setting
{
    public array $settings = [];

    public string $settingFile = '';

    /**
     * @param string $settingFile
     *
     * @return void
     */
    public function setSettingFile(string $settingFile): void
    {
        $this->settingFile = $settingFile;
    }

    /**
     * Set settings
     *
     * @return void
     */
    private function setSettingConfig(): void
    {
        $json = file_get_contents($this->settingFile);
        $this->settings = json_decode($json, true);
    }

    /**
     * @return bool
     */
    public function isAllEventsNotification(): bool
    {
        if (!empty($this->settings)
            && $this->settings[SettingConstant::T_ALL_EVENTS_NOTIFICATION] === true
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isNotified(): bool
    {
        if (!empty($this->settings)
            && $this->settings[SettingConstant::T_IS_NOTIFIED] === true
        ) {
            return true;
        }

        return false;
    }

    /**
     * Update setting item value and save to file
     *
     * @param string $settingName
     * @param $settingValue
     *
     * @return bool
     */
    public function updateSettingItem(
        string $settingName,
        $settingValue = null
    ): bool {
        $settingKeys = explode('.', $settingName);
        $lastKey = array_pop($settingKeys);
        $nestedSettings = &$this->settings;

        foreach ($settingKeys as $key) {
            if (!isset($nestedSettings[$key])
                || !is_array($nestedSettings[$key])
            ) {
                return false;
            }
            $nestedSettings = &$nestedSettings[$key];
        }

        if (isset($nestedSettings[$lastKey])) {
            $newValue = $settingValue ?? !$nestedSettings[$lastKey];
            $nestedSettings[$lastKey] = $newValue;

            return $this->saveSettingsToFile();
        }

        return false;
    }

    /**
     * Save settings to json file
     *
     * @return bool
     */
    private function saveSettingsToFile(): bool
    {
        if (file_exists($this->settingFile)) {
            $json = json_encode($this->settings, JSON_PRETTY_PRINT);
            file_put_contents($this->settingFile, $json, LOCK_EX);

            return true;
        }

        return false;
    }
}
