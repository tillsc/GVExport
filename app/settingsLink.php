<?php

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Auth;

class settingsLink
{
    const TOKEN_PREFIX = "?t=";
    const TOKEN_LENGTH = 10;
    private string $base_url;
    private $module;
    private $tree;
    private $user;
    private string $id;

    public function __construct($module, $tree, $id)
    {
        if (Settings::isUserLoggedIn()) {
            $this->module = $module;
            $this->tree = $tree;
            $this->user = Auth::user()->id();
            $this->id = $id;
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
            $record[$token]['user'] = $this->user;
            $record[$token]['tree'] = $this->tree;
            $record[$token]['settings_id'] = $this->id;
            $this->setSharedSettingsList($record);
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
}