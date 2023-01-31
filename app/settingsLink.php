<?php

namespace vendor\WebtreesModules\gvexport;

use Cassandra\Set;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Services\GedcomImportService;
use Fisharebest\Webtrees\Services\TreeService;

class settingsLink
{
    const TOKEN_PREFIX = "?t=";
    const TOKEN_LENGTH = 10;
    private string $base_url;
    private $module;
    private $tree;
    private $userId;
    private string $id;
    private Settings $settings_obj;

    public function __construct($module, $tree, $parent_obj, $id = "")
    {
        $this->module = $module;
        $this->tree = $tree;
        $this->id = $id;
        $this->settings_obj = $parent_obj;
        if (Settings::isUserLoggedIn()) {
            $this->userId = Auth::user()->id();
            $this->base_url = $module->base_url;
        }
    }

    /**
     * @throws \Exception
     */
    public function getUrl(): string
    {
        try {
            $record = $this->getSharedSettingsList();
        } catch (\Exception $error) {
            throw new \Exception($error);
        }

        foreach ($record as $key=>$value) {
            if ($value['settings_id'] == $this->id) {
                $token = $key;
            }
        }

        if (!isset($token)) {
            // Capital letters only, and removed 0 and O to try to reduce transcribing errors
            do {
                $token = substr(str_shuffle(str_repeat("123456789ABCDEFGHIJKLMNPQRSTUVWXYZ", self::TOKEN_LENGTH)), 0, self::TOKEN_LENGTH);
            } while (isset($record[$token]));
            $record[$token]['user'] = $this->userId;
            $record[$token]['tree'] = $this->tree->id();
            $record[$token]['settings_id'] = $this->id;
            $this->setSharedSettingsList($record);
            $this->updateSettingsWithToken($token);
        }

        return $this->base_url . self::TOKEN_PREFIX . $token;
    }

    /**
     * @throws \Exception
     */
    private function getSharedSettingsList()
    {
        $pref = $this->module->getPreference(Settings::PREFERENCE_PREFIX . Settings::SAVED_SETTINGS_LIST_PREFERENCE_NAME, "preference not set");
        if ($pref == "preference not set") {
            return [];
        } else {
            $shared_settings = json_decode($pref, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(json_last_error());
            }
            return $shared_settings;
        }
    }
    private function setSharedSettingsList($record)
    {
        $json = json_encode($record);
        $this->module->setPreference(Settings::PREFERENCE_PREFIX . Settings::SAVED_SETTINGS_LIST_PREFERENCE_NAME, $json);
    }

    /**
     * @throws \Exception
     */
    public function loadToken(String $token, Settings $settings): array
    {
        $shared_settings_list = $this->getSharedSettingsList();
        $this->userId = $shared_settings_list[$token]['user'];
        $tree_service    = new TreeService(new GedcomImportService());
        $this->tree = $tree_service->find($shared_settings_list[$token]['tree']);
        $this->id = $shared_settings_list[$token]['settings_id'];
        if (isset($shared_settings_list[$token])) {
            $userSettings = $settings->loadUserSettings($this->module, $this->tree, $this->id, $this->userId);
            $preferences = [];
            foreach ($settings->getDefaultSettings() as $preference => $value) {
                if (Settings::shouldLoadSetting($preference)) {
                    $preferences[$preference] = $userSettings[$preference];
                }
            }
            return $preferences;
        } else {
            throw new \Exception("Invalid token");
        }
    }

    public function removeTokenRecord(): bool
    {
        try {
            $sharedSettingsList = $this->getSharedSettingsList();
            foreach ($sharedSettingsList as $key=>$value) {
                if ($value['user'] === Auth::user()->id() &&
                    $value['tree'] === $this->tree->id() &&
                    $value['settings_id'] === $this->id) {
                    unset($sharedSettingsList[$key]);
                }
            }
            $this->setSharedSettingsList($sharedSettingsList);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateSettingsWithToken($token)
    {
        if (Auth::user()->id() == Settings::GUEST_USER_ID) {
            return false;
        } else {
            $settings = $this->settings_obj->loadUserSettings($this->module, $this->tree, $this->id);
            $settings['token'] = $token;
            $this->settings_obj->saveUserSettings($this->module, $this->tree, $settings, $this->id);
        }
    }
}