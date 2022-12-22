<?php

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Auth;

class Settings
{
    private const GUEST_USER_ID = 0;
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
     * @param $module
     * @param bool $reset
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
     * @param bool $reset
     * @return array
     */
    public function getAdminSettings($module): array
    {
        $settings = $this->defaultSettings;
        foreach ($settings as $preference => $value) {
            $pref = $module->getPreference($preference, "preference not set");
            if ($pref != "preference not set") {
                $settings[$preference] = $pref;
            }
        }
        return $settings;
    }

    /**
     * Retrieve the user settings from webtrees storage
     *
     * @param $module
     * @param $tree
     * @param bool $reset
     * @return array
     */
    public function loadUserSettings($module, $tree, bool $reset = false): array
    {
        $settings = $this->getAdminSettings($module);
        if (!$reset) {
            if (Auth::user()->id() == Settings::GUEST_USER_ID) {
                $cookie = new Cookie($tree);
                $settings = $cookie->load($settings);
            } else {
                foreach ($settings as $preference => $value) {
                    $pref = $tree->getUserPreference(Auth::user(), "GVE_" . $preference, "preference not set");
                    if ($pref != "preference not set") {
                        $settings[$preference] = $pref;
                    }
                }
            }
            if ($settings['use_graphviz'] == 'no' && $settings['graphviz_bin'] != "") {
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
        foreach ($settings as $preference=>$value) {
            $module->setPreference($preference, $value);
        }
    }

    /**
     *  Save the provided settings to webtrees user per-tree storage
     *
     * @param $tree
     * @param $settings
     * @return void
     */
    public function saveUserSettings($tree, $settings) {
        if (Auth::user()->id() == Settings::GUEST_USER_ID) {
            $cookie = new Cookie($tree);
            $cookie->set($settings);
        } else {
            foreach ($settings as $preference => $value) {
                if (Settings::shouldSaveSetting($preference)) {
                    $tree->setUserPreference(Auth::user(), "GVE_" . $preference, $value);
                }
            }
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
     * @return bool
     */
    public static function shouldSaveSetting(string $preference): bool
    {
        switch ($preference) {
            case 'graphviz_bin':
            case 'graphviz_config':
            case 'typefaces':
            case 'typeface_fallback':
            case 'directions':
            case 'use_abbr_places':
            case 'use_abbr_names':
            case 'countries':
            case 'temp_dir':
                return false;
            default:
                return true;
        }
    }
}