<?php

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Http\Exceptions\HttpBadRequestException;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Illuminate\Database\Capsule\Manager as DB;

class Settings
{
    public const ID_MAIN_SETTINGS = "_MAIN_";
    public const ID_ALL_SETTINGS = "_ALL_";
    private const GUEST_USER_ID = 0;
    private const ADMIN_PREFERENCE_NAME = "Admin_settings";
    private const PREFERENCE_PREFIX = "Settings";
    public const SETTINGS_LIST_PREFERENCE_NAME = "id_list_";
    const TREE_PREFIX = "_t_";
    const USER_PREFIX = "_u_";
    const ID_PREFIX = "_id_";
    const MAX_SETTINGS_ID_LIST_LENGTH = 250;
    const MAX_SETTINGS_ID_LENGTH = 6;
    private array $defaultSettings;
    public function __construct(){
        // Load settings from config file
        $this->defaultSettings = include dirname(__FILE__) . "/../config.php";
        // Add options lists
        $this->defaultSettings['typefaces'] = [0 => "Arial", 10 => "Brush Script MT", 20 => "Courier New", 30 => "Garamond", 40 => "Georgia", 50 => "Tahoma", 60 => "Times New Roman", 70 => "Trebuchet MS", 80 => "Verdana"];
        $this->defaultSettings['typeface_fallback'] = [0 => "Sans",  10 => "Cursive", 20 => "Monospace", 30 => "Serif", 40 => "Serif", 50 => "Sans", 60 => "Serif", 70 => "Sans", 80 => "Sans"];
        $this->defaultSettings['directions']['TB'] = "Top-to-bottom";
        $this->defaultSettings['directions']['LR'] = "Left-to-right";
        $this->defaultSettings['use_abbr_places'] = [0 => "Full place name", 10 => "City and country" ,  20 => "City and 2 letter ISO country code", 30 => "City and 3 letter ISO country code"];
        $this->defaultSettings['use_abbr_names'] = [0 => "Full name", 10 => "Given and surnames", 20 => "Given names" , 30 => "First given name only", 40 => "Surnames", 50 => "Initials only", 60 => "Given name initials and surname", 70 => "Don't show names"];
        $this->defaultSettings['countries'] = $this->getCountryAbbreviations();
        if (!$this->isGraphvizAvailable($this->defaultSettings['graphviz_bin'])) {
            $this->defaultSettings['graphviz_bin'] = "";
        }
        $this->defaultSettings['graphviz_config'] = $this->getGraphvizSettings($this->defaultSettings);
    }

    /**
     * Retrieve the currently set default settings from the admin page
     *
     * @return array
     */
    public function getDefaultSettings(): array
    {
        return $this->defaultSettings;
    }

    /**
     * Retrieve the currently set default settings from the admin page
     *
     * @param $module
     * @return array
     */
    public function getAdminSettings($module): array
    {
        $settings = $this->defaultSettings;
        $loaded = $module->getPreference(self::ADMIN_PREFERENCE_NAME, "preference not set");
        if ($loaded != "preference not set") {
            $loaded_settings = json_decode($loaded, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($settings as $preference => $value) {
                    if (self::shouldLoadSetting($preference, true)) {
                        $pref = $loaded_settings[$preference];
                        if ($pref == 'true' || $pref == 'false') {
                            $settings[$preference] = ($pref == 'true');
                        } else {
                            $settings[$preference] = $pref;
                        }
                    }
                }
            } else {
                throw new HttpBadRequestException(I18N::translate('Invalid JSON') . " 1: " . json_last_error_msg() . $loaded);
            }
        }
        $settings['graphviz_config'] = $this->getGraphvizSettings($settings);
        return $settings;

    }

    /**
     * Retrieve the user settings from webtrees storage
     *
     * @param $module
     * @param $tree
     * @param bool $reset
     * @param string $id
     * @return array
     */
    public function loadUserSettings($module, $tree, string $id = self::ID_MAIN_SETTINGS, bool $reset = false): array
    {
        $id_suffix = $id === self::ID_MAIN_SETTINGS ? "" : "_" . self::ID_PREFIX . $id;
        $settings = $this->getAdminSettings($module);
        if (!$reset) {
            if (Auth::user()->id() == self::GUEST_USER_ID) {
                $cookie = new Cookie($tree);
                $settings = $cookie->load($settings);
            } else {
                $loaded = $module->getPreference(self::PREFERENCE_PREFIX . self::TREE_PREFIX . $tree->id() . self::USER_PREFIX . Auth::user()->id() . $id_suffix, "preference not set");
                if ($loaded != "preference not set") {
                $loaded_settings = json_decode($loaded, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        foreach ($settings as $preference => $value) {
                            if (self::shouldLoadSetting($preference)) {
                                $pref = $loaded_settings[$preference];
                                if ($pref == 'true' || $pref == 'false') {
                                    $settings[$preference] = ($pref == 'true');
                                } else {
                                    $settings[$preference] = $pref;
                                }
                            }
                        }
                    } else {
                        throw new HttpBadRequestException(I18N::translate('Invalid JSON') . " 1: " . json_last_error_msg() . $loaded);
                    }
                } else {
                    $settings['first_run_xref_check'] = true;
                }
            }
            if (!$settings['enable_graphviz'] && $settings['graphviz_bin'] != "") {
                $settings['graphviz_bin'] = "";
            }
        }
        return $settings;
    }

    /**
     *  Save the provided settings to webtrees admin storage
     *
     * @param $module
     * @param $settings
     * @return void
     */
    public function saveAdminSettings($module, $settings) {
        $saveSettings = $this->defaultSettings;
        $s = [];
        foreach ($saveSettings as $preference=>$value) {
            if (self::shouldSaveSetting($preference, true)) {
                if (isset($settings[$preference])) {
                    if (gettype($value) == 'boolean') {
                        $s[$preference] = ($settings[$preference] ? 'true' : 'false');
                    } else {
                        $s[$preference] = $settings[$preference];
                    }
                } else {
                    $s[$preference] = 'false';
                }
            }
        }
        $json = json_encode($s);
        $module->setPreference(self::ADMIN_PREFERENCE_NAME, $json);
    }

    /**
     *  Save the provided settings to webtrees user per-tree storage
     *
     * @param $tree
     * @param $settings
     * @param string $id
     * @return bool
     */
    public function saveUserSettings($module, $tree, $settings, string $id = self::ID_MAIN_SETTINGS): bool
    {
        $id_suffix = $id === self::ID_MAIN_SETTINGS ? "" : "_" . self::ID_PREFIX . $id;
        if (Auth::user()->id() == self::GUEST_USER_ID) {
            if ($id == self::ID_MAIN_SETTINGS) {
                $cookie = new Cookie($tree);
                $cookie->set($settings);
                return true;
            } else {
                return false; // Logged-out users are handled in local storage, this should never happen
            }
        } else {
            $s = [];
            foreach ($settings as $preference => $value) {
                if (self::shouldSaveSetting($preference)) {
                    if (gettype($value) == 'boolean') {
                        $s[$preference] = ($value ? 'true' : 'false');
                    } else {
                        $s[$preference] = $value;
                    }
                }
            }
            $json = json_encode($s);
            $module->setPreference(self::PREFERENCE_PREFIX . self::TREE_PREFIX . $tree->id() . self::USER_PREFIX . Auth::user()->id() . $id_suffix, $json);
            return true;
        }
    }

    public function deleteUserSettings($module, $tree, $id) {
        $id_suffix = ($id === self::ID_MAIN_SETTINGS ? "" : "_" . self::ID_PREFIX . $id);
        if (Settings::isUserLoggedIn()) {
            // Preference isn't actually deleted. Will be cleaned up by webtrees when module removed, or reused
            // if ID count is reset by removing all saved settings from UI. webtrees does not provide
            // functionality to delete preference.
            $module->setPreference(self::PREFERENCE_PREFIX . self::TREE_PREFIX . $tree->id() . self::USER_PREFIX . Auth::user()->id() . $id_suffix, "");

            $ids = explode(',', $this->getSettingsIdList($module, $tree));
            while(($i = array_search($id, $ids)) !== false) {
                unset($ids[$i]);
            }
            $i = array_search($id, $ids);
            if ($i) {
                unset($ids[$i]);
            }
            $id_list = implode(',', $ids);
            $module->setPreference(self::PREFERENCE_PREFIX . self::SETTINGS_LIST_PREFERENCE_NAME . $tree->id(), $id_list);
        }
    }

    /**
     * Check if exec function is available to prevent error if webserver has disabled it
     * From: https://stackoverflow.com/questions/3938120/check-if-exec-is-disabled
     * @return bool
     */
    private function is_exec_available(): bool
    {
        static $available;

        if (!isset($available)) {
            $available = true;
            if (ini_get('safe_mode')) {
                $available = false;
            } else {
                $d = ini_get('disable_functions');
                $s = ini_get('suhosin.executor.func.blacklist');
                if ("$d$s") {
                    $array = preg_split('/,\s*/', "$d,$s");
                    if (in_array('exec', $array)) {
                        $available = false;
                    }
                }
            }
        }

        return $available;
    }

    /**
     * Check if Graphviz is available
     *
     * @param $binPath
     * @return mixed|string
     */
    private function isGraphvizAvailable($binPath)
    {
        static $outcome;

        if (!isset($outcome)) {
            if ($binPath == "") {
                $outcome = false;
                return false;
            }
            $stdout_output = null;
            $return_var = null;
            if ($this->is_exec_available()) {
                exec($binPath . " -V" . " 2>&1", $stdout_output, $return_var);
            }
            if (!$this->is_exec_available() || $return_var !== 0) {
                $outcome = false;
            } else {
                $outcome = true;
            }
        }
        return $outcome;
    }

    /**
     * Load country data for abbreviating place names
     * Data comes from https://www.datahub.io/core/country-codes
     * This material is licensed by its maintainers under the Public Domain Dedication and License, however,
     * they note that the data is ultimately sourced from ISO who have an unclear licence regarding use,
     * particularly around commercial use. Though all data sources providing ISO data have this problem.
     * @return array
     */
    private function getCountryAbbreviations(): array
    {
        $string = file_get_contents(dirname(__FILE__) . "/../resources/data/country-codes_json.json");
        $json = json_decode($string, true);
        $countries = [];
        foreach ($json as $row) {
            $countries['iso2'][strtolower($row['Name'])] = $row['ISO3166-1-Alpha-2'];
            $countries['iso3'][strtolower($row['Name'])] = $row['ISO3166-1-Alpha-3'];
        }
        return $countries;
    }

    private function getGraphvizSettings($settings): array
    {
        // Output file formats
        $Graphviz['output']['svg']['label'] = "SVG"; #ESL!!! 20090213
        $Graphviz['output']['svg']['extension'] = "svg";
        $Graphviz['output']['svg']['exec'] = $settings['graphviz_bin'] . " -Tsvg:cairo -o" . $settings['filename'] . ".svg " . $settings['filename'] . ".dot";
        $Graphviz['output']['svg']['cont_type'] = "image/svg+xml";

        $Graphviz['output']['dot']['label'] = "DOT"; #ESL!!! 20090213
        $Graphviz['output']['dot']['extension'] = "dot";
        $Graphviz['output']['dot']['exec'] = "";
        $Graphviz['output']['dot']['cont_type'] = "text/plain; charset=utf-8";

        $Graphviz['output']['png']['label'] = "PNG"; #ESL!!! 20090213
        $Graphviz['output']['png']['extension'] = "png";
        $Graphviz['output']['png']['exec'] = $settings['graphviz_bin'] . " -Tpng -o" . $settings['filename'] . ".png " . $settings['filename'] . ".dot";
        $Graphviz['output']['png']['cont_type'] = "image/png";

        $Graphviz['output']['jpg']['label'] = "JPG"; #ESL!!! 20090213
        $Graphviz['output']['jpg']['extension'] = "jpg";
        $Graphviz['output']['jpg']['exec'] = $settings['graphviz_bin'] . " -Tjpg -o" . $settings['filename'] . ".jpg " . $settings['filename'] . ".dot";
        $Graphviz['output']['jpg']['cont_type'] = "image/jpeg";

        $Graphviz['output']['pdf']['label'] = "PDF"; #ESL!!! 20090213
        $Graphviz['output']['pdf']['extension'] = "pdf";
        $Graphviz['output']['pdf']['exec'] = $settings['graphviz_bin'] . " -Tpdf -o" . $settings['filename'] . ".pdf " . $settings['filename'] . ".dot";
        $Graphviz['output']['pdf']['cont_type'] = "application/pdf";

        if ( !empty( $settings['graphviz_bin']) && $settings['graphviz_bin'] != "") {

            $Graphviz['output']['gif']['label'] = "GIF"; #ESL!!! 20090213
            $Graphviz['output']['gif']['extension'] = "gif";
            $Graphviz['output']['gif']['exec'] = $settings['graphviz_bin'] . " -Tgif -o" . $settings['filename'] . ".gif " . $settings['filename'] . ".dot";
            $Graphviz['output']['gif']['cont_type'] = "image/gif";

            $Graphviz['output']['ps']['label'] = "PS"; #ESL!!! 20090213
            $Graphviz['output']['ps']['extension'] = "ps";
            $Graphviz['output']['ps']['exec'] = $settings['graphviz_bin'] . " -Tps2 -o" . $settings['filename'] . ".ps " . $settings['filename'] . ".dot";
            $Graphviz['output']['ps']['cont_type'] = "application/postscript";
        }

        return $Graphviz;
    }

    /**
     * Returns whether a setting shouldn't be saved to cookies/preferences
     *
     * @param string $preference
     * @param bool $admin
     * @return bool
     */
    public static function shouldSaveSetting(string $preference, bool $admin = false): bool
    {
        switch ($preference) {
            case 'graphviz_bin':
            case 'graphviz_config':
            case 'typefaces':
            case 'typeface_fallback':
            case 'default_typeface':
            case 'directions':
            case 'use_abbr_places':
            case 'use_abbr_names':
            case 'countries':
            case 'temp_dir':
            case 'space_base':
            case 'no_fams':
            case 'stop_proc':
                return false;
            case 'show_debug_panel':
            case 'filename':
            case 'mclimit':
            case 'birth_prefix':
            case 'death_prefix':
                return $admin;
            default:
                return true;
        }
    }

    /**
     * Currently an alias for shouldLoadSetting as the criteria are the same
     *
     * @param $setting
     * @param bool $admin
     * @return bool
     */
    public static function shouldLoadSetting($setting, bool $admin = false): bool
    {
        return self::shouldSaveSetting($setting, $admin);
    }

    public function getSettingsJson($module, $tree, $id)
    {
        $userSettings = $this->loadUserSettings($module, $tree, $id);
        $settings = [];

        foreach ($this->defaultSettings as $preference => $value) {
            if (self::shouldSaveSetting($preference)) {
                $settings[$preference] = $userSettings[$preference];
            }
        }
        return json_encode($settings);
    }

    public function getAllSettingsJson($module, $tree)
    {
        $settings_list = array();
        $id_list = $this->getSettingsIdList($module, $tree);
        $ids = explode(',', $id_list);
        if ($ids != "") {
            foreach ($ids as $id_value) {
                if ($id_value != "") {
                    $userSettings = $this->loadUserSettings($module, $tree, $id_value);
                    $settings_list[(string) $id_value]['name'] = $userSettings['save_settings_name'];
                    $settings_list[(string) $id_value]['id'] = $id_value;
                    $settings = array();
                    foreach ($this->defaultSettings as $preference => $value) {
                        if (self::shouldSaveSetting($preference)) {
                            $settings[$preference] = $userSettings[$preference];
                        }
                    }
                    $settings_list[(string) $id_value]['settings'] = json_encode($settings);
                }
            }
        }
        return json_encode($settings_list);
    }

    public function newSettingsId($module, $tree): string
    {
        $id_list = $this->getSettingsIdList($module, $tree);

        if ($id_list == "") {
            $id_list = "0";
            $new_id = "0";
        } else {
            $preferences = explode(',', $id_list);
            $last_id = end($preferences);
            if ($last_id != "") {
                $next_id = (int)base_convert($last_id, 36, 10) + 1;
                $new_id = base_convert($next_id, 10, 36);
                $id_list = $id_list . "," . $new_id;
                if (strlen($id_list) > self::MAX_SETTINGS_ID_LIST_LENGTH || strlen($new_id) > self::MAX_SETTINGS_ID_LENGTH) {
                    return "";
                }
            } else {
                return "";
            }
        }

        $use_cookie = Auth::user()->id() == self::GUEST_USER_ID;
        if ($use_cookie) {
            return ""; // Logged-out users are handled in local storage, this should never happen
        } else {
            $module->setPreference(self::PREFERENCE_PREFIX . self::SETTINGS_LIST_PREFERENCE_NAME . $tree->id(), $id_list);
        }

        return $new_id;
    }

    private function getSettingsIdList($module, $tree) {
        $user = Auth::user()->id();
        $use_cookie = $user == self::GUEST_USER_ID;
        if ($use_cookie) {
            return ""; // Logged-out users are handled via local storage, so this should never happen
        } else {
            $pref_list = $module->getPreference(self::PREFERENCE_PREFIX . self::SETTINGS_LIST_PREFERENCE_NAME . $tree->id(), "preference not set");
            if ($pref_list == "preference not set") {
                $pref_list = "";
            }
        }
        return $pref_list;
    }

    public static function isUserLoggedIn(): bool
    {
        if (Auth::user()->id() == self::GUEST_USER_ID) {
            return false;
        } else {
            return true;
        }
    }
}