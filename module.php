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

require_once dirname(__FILE__) . "/config.php";
require_once dirname(__FILE__) . "/app/functionsClippingsCart.php";

// Auto-load class files
spl_autoload_register(function ($class) {
    if (strpos($class, "\gvexport\\")) {
        $name = basename(dirname(__FILE__) . "/app/" . str_replace('\\', '/',$class . '.php'));
        include dirname(__FILE__) . "/app/" . $name;
    }
});

use Cassandra\Set;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
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
    public const CUSTOM_VERSION     = '2.1.16';
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
        return Auth::checkIndividualAccess($individual, false, true);
    }

    public function getChartAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);
        if (isset($request->getQueryParams()['xref'])) {
            $xref = $request->getQueryParams()['xref'];
        } else {
            $xref = $tree->getUserPreference(Auth::user(), UserInterface::PREF_TREE_ACCOUNT_XREF);
        }
        $individual = $this->getIndividual($tree, $tree->significantIndividual(Auth::user(), $xref)->xref());
		$userDefaultVars = (new Settings($this))->getSettings();
        if (!isset($_REQUEST['reset'])) {
            $cookie = new Cookie($tree);
            // Load settings from cookie *on top* of our default settings,
            // in case cookie does not have a value for all settings
            $userDefaultVars = $cookie->load($userDefaultVars);
        }

        $otypes = $this->getOTypes($userDefaultVars);

        return $this->viewResponse($this->name() . '::page', [
            'gvexport_css'  => route('module', ['module' => $this->name(), 'action' => 'Css']),
            'gvexport_js'  => route('module', ['module' => $this->name(), 'action' => 'JS']),
            'tree'          => $tree,
            'individual'    => $individual,
            'title'         => 'GVExport',
            'vars'          => $userDefaultVars,
            'otypes'        => $otypes,
            'cartempty'     => !functionsClippingsCart::isIndividualInCart($tree),
            'module'        => $this
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

        $outputFile = new OutputFile($temp_dir, $file_type, $this);
        return $outputFile->downloadFile();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function getAdminAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';
        $otypes = $this->getOTypes((new Settings($this))->getSettings());
        $response['module'] = $this;
        $response['otypes'] = $otypes;
        $response['vars'] = (new Settings($this))->getSettings(isset($_REQUEST['reset']) && $_REQUEST['reset'] === "1");
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
            (new Settings($this))->saveAdminSettings($params['vars']);
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
        // Make a unique directory to the tmp dir
        $temp_dir = (new File())->sys_get_temp_dir_my() . "/" . md5(Auth::id());
        if (!is_dir("$temp_dir")) {
            mkdir("$temp_dir");
        }

        // Create the dump
        $contents = $this->createGraphVizDump($tree, $temp_dir);

        // Put the contents into the file
        $settings = (new Settings($this))->getSettings();
        $fid = fopen($temp_dir . "/" . $settings['filename'] . ".dot", "w");
        fwrite($fid, $contents);
        fclose($fid);

        return $temp_dir;
    }

    function createGraphVizDump($tree, $temp_dir): string
    {
        $out = "";
        $dot = new Dot($tree, $this, Registry::filesystem()->data());
        $vars = $_REQUEST['vars'];

        $cookie = new Cookie($tree);
        $cookie->set($vars);


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
        if (!empty($vars["other_stop_pids"])) {
            $dot->setSettings("stop_pids", $vars["other_stop_pids"]);
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
            $dot->setSettings('color_arrow_related', $vars['color_arrow_related']);
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
        if (isset($vars['adv_files'])) {
            $dot->setSettings("adv_files", $vars['adv_files']);
        }

        if (isset($vars['auto_update'])) {
            $dot->setSettings("auto_update", "auto_update");
        }

        if (isset($vars['debug']) && $vars['debug'] == "debug") {
            $dot->setSettings("debug", TRUE);
        }

        // Set custom colors
        if (isset($_REQUEST["vars"]["colorm"])) {
            $dot->setSettings("colorm", $_REQUEST["vars"]["colorm"]);
        }
        if (isset($_REQUEST["vars"]["colorf"])) {
            $dot->setSettings("colorf", $_REQUEST["vars"]["colorf"]);
        }
        if (isset($_REQUEST["vars"]["colorx"])) {
            $dot->setSettings("colorx", $_REQUEST["vars"]["colorx"]);
        }
        if (isset($_REQUEST["vars"]["coloru"])) {
            $dot->setSettings("coloru", $_REQUEST["vars"]["coloru"]);
        }
        if (isset($_REQUEST["vars"]["colorm_nr"])) {
            $dot->setSettings("colorm_nr", $_REQUEST["vars"]["colorm_nr"]);
        }
        if (isset($_REQUEST["vars"]["colorf_nr"])) {
            $dot->setSettings("colorf_nr", $_REQUEST["vars"]["colorf_nr"]);
        }
        if (isset($_REQUEST["vars"]["colorx_nr"])) {
            $dot->setSettings("colorx_nr", $_REQUEST["vars"]["colorx_nr"]);
        }
        if (isset($_REQUEST["vars"]["coloru_nr"])) {
            $dot->setSettings("coloru_nr", $_REQUEST["vars"]["coloru_nr"]);
        }
        if (isset($_REQUEST["vars"]["colorfam"])) {
            $dot->setSettings("colorfam", $_REQUEST["vars"]["colorfam"]);
        }
        if (isset($_REQUEST["vars"]["colorbg"])) {
            $dot->setSettings("colorbg", $_REQUEST["vars"]["colorbg"]);
        }
        if (isset($_REQUEST["vars"]["colorindibg"])) {
            $dot->setSettings("colorindibg", $_REQUEST["vars"]["colorindibg"]);
        }
        if (isset($_REQUEST["vars"]["startcol"])) {
            $dot->setSettings("startcol", $_REQUEST["vars"]["startcol"]);
        }
        if (isset($_REQUEST["vars"]["colorstartbg"])) {
            $dot->setSettings("colorstartbg", $_REQUEST["vars"]["colorstartbg"]);
        }
        if (isset($_REQUEST["vars"]["colorborder"])) {
            $dot->setSettings("colorborder", $_REQUEST["vars"]["colorborder"]);
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
        $out .= $dot->createDOTDump();
        if (isset($_POST["browser"]) && $_POST["browser"] == "true") {
            $dot->messages[] = I18N::translate('Generated %s individuals and %s family records', sizeof($dot->individuals), sizeof($dot->families));
            $response['messages'] = $dot->messages;
            $response['debug'] = $dot->debug_string;
            $response['dot'] = $out;
            $cookie = new Cookie($tree);
            $response['settings'] = json_encode($cookie->load([]));
            $r = json_encode($response);
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

    /** Return list of available output types
     *
     * @return array
     */
    private function getOTypes($vars): array
    {
        $otypes = array();
        foreach ($vars['graphviz_config']["output"] as $fmt => $val) {
            if (isset($vars['graphviz_config']["output"][$fmt]["label"]) and isset($vars['graphviz_config']["output"][$fmt]["extension"])) {
                $lbl = $vars['graphviz_config']["output"][$fmt]["label"];
                $ext = $vars['graphviz_config']["output"][$fmt]["extension"];
                $otypes[$ext] = $lbl;
            }
        }
        return $otypes;
    }
}

return new GVExport();