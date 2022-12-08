<?php

namespace vendor\WebtreesModules\gvexport;

/**
 * A cookie object for saving settings
 */
class Cookie
{
    private string $name;

    /**
     * Name for the cookie is generated based on tree name
     * @param $tree
     */
    function __construct($tree) {
        $this->name = $this->getName($tree);
    }

    /**
     * Return the name of the cookie
     *
     * @param $tree
     * @return string
     */
    private function getName($tree): string
    {
        // Get name of tree from webtrees
        $name = $tree->name();
        // Replace space with underscore
        $name =  preg_replace('/\s/', '_', $name);
        // alphanumeric / underscore characters only
        $name = preg_replace('/[^a-z0-9\s_]/i', '', $name);

        return "GVEUserDefaults_" . $name;
    }

    /**
     * Save the settings in a cookie
     *
     * @param $vars
     * @return void
     */
    public function set($vars) {
        $cookieStr = "";
        foreach ($vars as $key => $value) {
            $cookieStr .= "$key=$value|";
        }
        setcookie($this->name, $cookieStr, time() + (3600 * 24 * 365));
    }

    /**
     * Load settings from cookie over the default settings
     *
     * @param $userDefaultVars
     * @return array
     */
    public function load($userDefaultVars): array
    {
        if (isset($_COOKIE[$this->name]) and $_COOKIE[$this->name] != "") {
            foreach (explode("|", $_COOKIE[$this->name]) as $s) {
                $arr = explode("=", $s);
                if (count($arr) == 2) {
                    $userDefaultVars[$arr[0]] = $arr[1];
                }
            }
        }
        return $userDefaultVars;
    }
}