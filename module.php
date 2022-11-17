<?php

/**
 * GraphViz module for Webtrees
 *
 * Ported to Webtrees by Iain MacDonald <ijmacd@gmail.com>
 */
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

namespace vendor\WebtreesModules\gvexport;

require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/app/utils.php");
require_once(dirname(__FILE__) . "/app/functionsClippingsCart.php");
require_once(dirname(__FILE__) . "/app/functionsAdmin.php");
require_once(dirname(__FILE__) . "/app/OutputFile.php");
require_once(dirname(__FILE__) . "/app/Person.php");

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleChartTrait;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Webtrees;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;


/**
 * Main class for GVExport module
 */
class GVExport extends AbstractModule implements ModuleCustomInterface, ModuleChartInterface, ModuleConfigInterface
{

    use ModuleCustomTrait;
    use ModuleChartTrait;
    use ModuleConfigTrait;
    public const CUSTOM_VERSION     = '2.1.15';
    public const CUSTOM_MODULE      = "GVExport";
    public const CUSTOM_LATEST      = 'https://raw.githubusercontent.com/Neriderc/' . self::CUSTOM_MODULE. '/main/latest-version.txt';
    public const SUPPORT_URL        = 'https://github.com/Neriderc/GVExport';

    public function boot(): void
    {
        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    }

    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    public function title(): string
    {
        return 'GVExport';
    }

    public function description(): string
    {
        return 'This is the "GVExport" module';
    }

    public function chartMenuClass(): string
    {
        return 'menu-chart-familybook';
    }

    public function chartBoxMenu(Individual $individual): ?Menu
    {
        return $this->chartMenu($individual);
    }

    public function chartUrl(Individual $individual, array $parameters = []): string
    {
        return route('module', array_merge($parameters, [
            'module' => $this->name(),
            'action' => 'Chart',
            'xref' => $individual->xref(),
            'tree' => $individual->tree()->name(),
        ]));
    }

    /**
     * The version of this module.
     *
     * @return string
     */
    public function customModuleVersion(): string
    {
        return self::CUSTOM_VERSION;
    }

    /**
     * A URL that will provide the latest version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersionUrl(): string
    {
        return self::CUSTOM_LATEST;
    }

    /**
     * Where to get support for this module.
     *
     * @return string
     */
    public function customModuleSupportUrl(): string
    {
        return self::SUPPORT_URL;
    }

    public function getIndividual($tree, $xref): Individual
    {
        $individual = Registry::individualFactory()->make($xref, $tree);
        $individual = Auth::checkIndividualAccess($individual, false, true);

        return $individual;
    }

    public function getChartAction(ServerRequestInterface $request): ResponseInterface
    {
        global $GVE_CONFIG;

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $individual = $this->getIndividual($tree, $request->getQueryParams()['xref']);

		$userDefaultVars = getAdminSettings($this, false);
        if (!isset($_REQUEST['reset']) and isset($_COOKIE["GVEUserDefaults"]) and $_COOKIE["GVEUserDefaults"] != "") {
            foreach (explode("|", $_COOKIE["GVEUserDefaults"]) as $s) {
                $arr = explode("=", $s);
                if (count($arr) == 2) {
                    $userDefaultVars[$arr[0]] = $arr[1];
                }
            }
        }

        $otypes = array();
        foreach ($GVE_CONFIG["output"] as $fmt => $val) {
            if (isset($GVE_CONFIG["output"][$fmt]["label"]) and isset($GVE_CONFIG["output"][$fmt]["extension"])) {
                $lbl = $GVE_CONFIG["output"][$fmt]["label"];
                $ext = $GVE_CONFIG["output"][$fmt]["extension"];
                $otypes[$ext] = $lbl;
            }
        }

        return $this->viewResponse($this->name() . '::page', [
            'gvexport_css'  => route('module', ['module' => $this->name(), 'action' => 'Css']),
            'gvexport_js'  => route('module', ['module' => $this->name(), 'action' => 'JS']),
            'tree'          => $tree,
            'individual'    => $individual,
            'title'         => 'GVExport',
            'vars'          => $userDefaultVars,
            'otypes'        => $otypes,
            'gve_config'    => $GVE_CONFIG,
            'cartempty'     => !functionsClippingsCart::isIndividualInCart($tree),
            'module'        => $this,
            'nographviz' => $GVE_CONFIG["graphviz_bin"] == ""
        ]);
    }

    /**
     * Where are the CCS specifications for this module stored?
     *
     * @return ResponseInterface
     *
     * @throws \JsonException
     */
    public function getCssAction() : ResponseInterface
    {
        return response(
            file_get_contents($this->resourcesFolder() . 'css' . DIRECTORY_SEPARATOR . 'gvexport.css'),
            200,
            ['content-type' => 'text/css']
        );
    }

    /**
     * Where are the Javascript functions for this module stored?
     *
     * @return ResponseInterface
     *
     * @throws \JsonException
     */
    public function getJSAction() : ResponseInterface
    {
        return response(
            file_get_contents($this->resourcesFolder() . 'javascript' . DIRECTORY_SEPARATOR . 'gvexport.js'),
            200,
            ['content-type' => 'text/javascript']
        );
    }

    public function postChartAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = $request->getAttribute('tree');
        $temp_dir = $this->saveDOTFile($tree);

        // If browser mode, output dot instead of selected file
        $file_type = isset($_POST["browser"]) && $_POST["browser"] == "true" ? "dot" : $_REQUEST["vars"]["otype"];

        $outputFile = new OutputFile($temp_dir, $file_type);
        return $outputFile->downloadFile();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        global $GVE_CONFIG;

        $this->layout = 'layouts/administration';

        $otypes = array();
        foreach ($GVE_CONFIG["output"] as $fmt => $val) {
            if (isset($GVE_CONFIG["output"][$fmt]["label"]) and isset($GVE_CONFIG["output"][$fmt]["extension"])) {
                $lbl = $GVE_CONFIG["output"][$fmt]["label"];
                $ext = $GVE_CONFIG["output"][$fmt]["extension"];
                $otypes[$ext] = $lbl;
            }
        }
        $response['module'] = $this;
        $response['otypes'] = $otypes;
        $response['vars'] = getAdminSettings($this, isset($_REQUEST['reset']) && $_REQUEST['reset'] === "1");
        $response['gve_config'] = $GVE_CONFIG;
        $response['title'] = $this->title();
        $response['gvexport_css']  = route('module', ['module' => $this->name(), 'action' => 'Css']);
        $response['gvexport_js']  = route('module', ['module' => $this->name(), 'action' => 'JS']);

        return $this->viewResponse($this->name() . '::' . 'settings', $response);
    }

    /**
     * save the user preferences in the database
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function postAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = (array) $request->getParsedBody();
        if ($params['save'] === '1') {
            saveAdminPreferences($params, $this);
            FlashMessages::addMessage(I18N::translate('The preferences for the module “%s” have been updated.',
                $this->title()), 'success');
        }
        return redirect($this->getConfigLink());
    }

    /**
     * Creates and saves a DOT file
     *
     * @return	string	Directory where the file is saved
     */
    function saveDOTFile($tree): string
    {
        global $GVE_CONFIG;

        // Make a unique directory to the tmp dir
        $temp_dir = sys_get_temp_dir_my() . "/" . md5(Auth::id());
        if (!is_dir("$temp_dir")) {
            mkdir("$temp_dir");
        }

        // Create the dump
        $contents = $this->createGraphVizDump($tree, $temp_dir);

        // Put the contents into the file
        $fid = fopen($temp_dir . "/" . $GVE_CONFIG["filename"] . ".dot", "w");
        fwrite($fid, $contents);
        fclose($fid);

        return $temp_dir;
    }

    function createGraphVizDump($tree, $temp_dir): string
    {
        require_once(dirname(__FILE__) . "/app/Dot.php");

        $out = "";
        $dot = new Dot($tree, Registry::filesystem()->data());

        $vars = $_REQUEST['vars'];

        $cookieStr = "";
        foreach ($vars as $key => $value)
            $cookieStr .= "$key=$value|";

        setcookie("GVEUserDefaults", $cookieStr, time() + (3600 * 24 * 365));

        if (isset($temp_dir)) {
            $dot->setSettings("temp_dir", $temp_dir);
        }

        // INDI id
        if (!empty($vars["other_pids"])) {
            $dot->setSettings("indi", $vars["other_pids"]);
        } else {
            $dot->setSettings("indi", "");
        }
        // Stop PIDs
        if (!empty($vars["other_stop_pids"]) || !empty($vars["stop_pid"])) {
            $dot->setSettings("stop_pids", $vars["stop_pid"] .','. $vars["other_stop_pids"]);
            $dot->setSettings("stop_proc", TRUE);
        } else {
            $dot->setSettings("stop_proc", FALSE);
        }

		if ($vars['indiance'] == 'ance') {
			$dot->setIndiSearchMethod("ance");
		}
        if ($vars['indidesc'] == 'desc') {
            $dot->setIndiSearchMethod("desc");
        }

        // If "Anyone" option is picked, then other relations options also must be set
		if ($vars['indisibl'] == 'sibl' || $vars['indiany'] == 'any') {
			$dot->setIndiSearchMethod("sibl");
		}
		if ($vars['indispou'] == 'spou' || $vars['indiany'] == 'any') {
			$dot->setIndiSearchMethod("spou");
		}
		if ($vars['indicous'] == 'cous' || $vars['indiany'] == 'any') {
			$dot->setIndiSearchMethod("cous");
		}
        if ($vars['indiany'] == 'any') {
            $dot->setIndiSearchMethod("any");
        }

		if (isset($vars['ance_level'])) {
			$dot->setSettings("ance_level", $_REQUEST["vars"]["ance_level"]);
		} else {
			$dot->setSettings("ance_level", 0);
		}
		if (isset($vars['desc_level'])) {
			$dot->setSettings("desc_level", $_REQUEST["vars"]["desc_level"]);
		} else {
			$dot->setSettings("desc_level", 0);
		}

        // If "Anyone" option is picked, then other relations options also must be set
        if ($vars['indisibl'] == 'sibl' || $vars['indiany'] == 'any') {
            $dot->setIndiSearchMethod("sibl");
        }
        if ($vars['indispou'] == 'spou' || $vars['indiany'] == 'any') {
            $dot->setIndiSearchMethod("spou");
        }
        if ($vars['indicous'] == 'cous' || $vars['indiany'] == 'any') {
            $dot->setIndiSearchMethod("cous");
        }
        if ($vars['indiany'] == 'any') {
            $dot->setIndiSearchMethod("any");
        }

        if (isset($vars['ance_level'])) {
            $dot->setSettings("ance_level", $_REQUEST["vars"]["ance_level"]);
        } else {
            $dot->setSettings("ance_level", 0);
        }
        if (isset($vars['desc_level'])) {
            $dot->setSettings("desc_level", $_REQUEST["vars"]["desc_level"]);
        } else {
            $dot->setSettings("desc_level", 0);
        }

        if (isset($_REQUEST["vars"]["mclimit"])) {
            $dot->setSettings("mclimit", $_REQUEST["vars"]["mclimit"]);
        }

        if ($vars['marknr'] == 'marknr') {
            $dot->setSettings("mark_not_related", TRUE);
        }
        if ($vars['fastnr'] == 'fastnr') {
            $dot->setSettings("fast_not_related", TRUE);
        }

        if (isset($vars['show_lt_editor'])) {
            $dot->setSettings("show_lt_editor", TRUE);
        }

        if (isset($vars['fontcolor_name'])) {
            $dot->setFontColorName($vars['fontcolor_name']);
        }

        if (isset($vars['fontcolor_details'])) {
            $dot->setFontColorDetails($vars['fontcolor_details']);
        }

        if (isset($vars['fontsize'])) {
            $dot->setFontSize($vars['fontsize'], 'base');
        }

        if (isset($vars['fontsize_name'])) {
            $dot->setFontSize($vars['fontsize_name'], 'name');
        }

        if (isset($vars['typeface'])) {
            $dot->setSettings("typeface", $vars['typeface']);
        }

        if (isset($vars['pagebrk'])) {
            $dot->setSettings("use_pagesize", $vars['psize']);
            $dot->setPageSize($vars['psize']);
        }

        if (isset($vars['arrow_default'])) {
            $dot->setArrowColour("default", $vars['arrow_default']);
        }

        if (isset($vars['arrow_related'])) {
            $dot->setArrowColour("related", $vars['arrow_related']);
        }

        if (isset($vars['arrow_not_related'])) {
            $dot->setArrowColour("not_related", $vars['arrow_not_related']);
        }

        if (isset($vars["color_arrow_related"])) {
            $dot->setColourArrowRelated($vars['color_arrow_related']);
        }

        if (isset($vars['grdir'])) {
            $dot->setSettings("graph_dir", $vars['grdir']);
        }

        // Which data to show
        if ($vars['show_by'] == 'show_by') {
            $dot->setSettings("show_by", TRUE);
        }
        if (isset($vars['bd_type'])) {
            $dot->setSettings("bd_type", $vars['bd_type']);
        }
        if ($vars['show_bp'] == 'show_bp') {
            $dot->setSettings("show_bp", TRUE);
        }
        if ($vars['show_dy'] == 'show_dy') {
            $dot->setSettings("show_dy", TRUE);
        }
        if (isset($vars['dd_type'])) {
            $dot->setSettings("dd_type", $vars['dd_type']);
        }
        if ($vars['show_dp'] == 'show_dp') {
            $dot->setSettings("show_dp", TRUE);
        }
        if ($vars['show_my'] == 'show_my') {
            $dot->setSettings("show_my", TRUE);
        }
        if (isset($vars['md_type'])) {
            $dot->setSettings("md_type", $vars['md_type']);
        }
        if ($vars['show_mp'] == 'show_mp') {
            $dot->setSettings("show_mp", TRUE);
        }
        if ($vars['show_pid'] == 'show_pid') {
            $dot->setSettings("show_pid", TRUE);
        }
        if ($vars['show_fid'] == 'show_fid') {
            $dot->setSettings("show_fid", TRUE);
        }

        if ($vars['show_url'] == 'show_url') {
            $dot->setSettings("show_url", TRUE);
        }

        if (isset($vars['use_abbr_place'])) {
            $dot->setSettings("use_abbr_place", $vars['use_abbr_place']);
        }

        if (isset($vars['use_abbr_name'])) {
            $dot->setSettings("use_abbr_name", $vars['use_abbr_name']);
        }

        if (isset($vars['usecart'])) {
            if ($_REQUEST["vars"]["usecart"] == "usecart") {
                $dot->setSettings("usecart", TRUE);
            } else {
                $dot->setSettings("usecart", FALSE);
            }
        }
        if (isset($vars['adv_people'])) {
            $dot->setSettings("adv_people", $vars['adv_people']);
        }
        if (isset($vars['adv_appear'])) {
            $dot->setSettings("adv_appear", $vars['adv_appear']);
        }

        if (isset($vars['auto_update'])) {
            $dot->setSettings("auto_update", "auto_update");
        }

        if (isset($vars['debug'])) {
            $dot->setSettings("debug", TRUE);
        }

        // Set custom colors
        if (isset($_REQUEST["vars"]["colorm"])) {
            $dot->setColor("colorm", $_REQUEST["vars"]["colorm"]);
        }
        if (isset($_REQUEST["vars"]["colorf"])) {
            $dot->setColor("colorf", $_REQUEST["vars"]["colorf"]);
        }
        if (isset($_REQUEST["vars"]["colorx"])) {
            $dot->setColor("colorx", $_REQUEST["vars"]["colorx"]);
        }
        if (isset($_REQUEST["vars"]["coloru"])) {
            $dot->setColor("coloru", $_REQUEST["vars"]["coloru"]);
        }
        if (isset($_REQUEST["vars"]["colorm_nr"])) {
            $dot->setColor("colorm_nr", $_REQUEST["vars"]["colorm_nr"]);
        }
        if (isset($_REQUEST["vars"]["colorf_nr"])) {
            $dot->setColor("colorf_nr", $_REQUEST["vars"]["colorf_nr"]);
        }
        if (isset($_REQUEST["vars"]["colorx_nr"])) {
            $dot->setColor("colorx_nr", $_REQUEST["vars"]["colorx_nr"]);
        }
        if (isset($_REQUEST["vars"]["coloru_nr"])) {
            $dot->setColor("coloru_nr", $_REQUEST["vars"]["coloru_nr"]);
        }
        if (isset($_REQUEST["vars"]["colorfam"])) {
            $dot->setColor("colorfam", $_REQUEST["vars"]["colorfam"]);
        }
        if (isset($_REQUEST["vars"]["colorbg"])) {
            $dot->setColor("colorbg", $_REQUEST["vars"]["colorbg"]);
        }
        if (isset($_REQUEST["vars"]["colorindibg"])) {
            $dot->setColor("colorindibg", $_REQUEST["vars"]["colorindibg"]);
        }
        if (isset($_REQUEST["vars"]["startcol"])) {
            $dot->setSettings("startcol", $_REQUEST["vars"]["startcol"]);
        }
        if (isset($_REQUEST["vars"]["colorstartbg"])) {
            $dot->setColor("colorstartbg", $_REQUEST["vars"]["colorstartbg"]);
        }
        if (isset($_REQUEST["vars"]["colorborder"])) {
            $dot->setColor("colorborder", $_REQUEST["vars"]["colorborder"]);
        }

        // Settings
        if (!empty($vars['diagtype'])) {
            $dot->setSettings("diagram_type", $vars['diagtype']);
            $dot->setSettings("diagram_type_combined_with_photo", $vars['with_photos'] == 'with_photos');
        }
        if (!empty($vars['no_fams'])) {
            $dot->setSettings("no_fams", $vars['no_fams']);
        }

        if (isset($vars['dpi'])) {
            $dot->setSettings("dpi", $vars['dpi']);
        }
        if (isset($vars['ranksep'])) {
            $dot->setSettings("ranksep", $vars['ranksep']);
        }
        if (isset($vars['nodesep'])) {
            $dot->setSettings("nodesep", $vars['nodesep']);
        }

        // Get out DOT file
        $out .= $dot->getDOTDump();
        if (isset($_POST["browser"]) && $_POST["browser"] == "true") {
            // Add in our counts of individuals and families so we can show a message
            $indinum = sizeof($dot->individuals);
            $famnum = sizeof($dot->families);
            // Add any error messages or other messages for showing toast
            $messageString = "";
            foreach ($dot->messages as $message) {
                $messageString .= "^".$message;
            }
            // Send our string of information
            $r = substr($messageString, 1) . "|". $indinum . "|" . $famnum . "|" . $out;
        } else {
            $r = $out;
        }
        return $r;
    }

    /**
     * Additional translations for module.
     *
     * @param string $language
     *
     * @return string[]
     */

    public function customTranslations(string $language): array
    {
        $lang_dir   = $this->resourcesFolder() . 'lang/';
        $file       = $lang_dir . $language . '.mo';
        if (file_exists($file)) {
            return (new Translation($file))->asArray();
        } else {
            return [];
        }
    }
}

return new GVExport();