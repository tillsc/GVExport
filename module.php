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
		$userDefaultVars = (new Settings())->getAdminSettings($this);

        if (!isset($_REQUEST['reset'])) {
            // Load settings from webtrees
            $settings = new Settings();
            $userDefaultVars = $settings->loadUserSettings($this, $tree);

        } else {
            if (!$userDefaultVars['use_graphviz'] && $userDefaultVars['graphviz_bin'] != "") {
                $userDefaultVars['graphviz_bin'] = "";
            }
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
        $otypes = $this->getOTypes((new Settings())->getAdminSettings($this));
        $response['module'] = $this;
        $response['otypes'] = $otypes;
        if (isset($_REQUEST['reset']) && $_REQUEST['reset'] === "1") {
            $response['vars'] = (new Settings())->getDefaultSettings();
        } else {
            $response['vars'] = (new Settings())->getAdminSettings($this);
        }

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
        $formSubmission = new FormSubmission();
        $vars = $formSubmission->load($_REQUEST['vars']);
        if ($params['save'] === '1') {
            (new Settings())->saveAdminSettings($this, $vars);
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
        $settings = (new Settings())->getAdminSettings($this);
        $fid = fopen($temp_dir . "/" . $settings['filename'] . ".dot", "w");
        fwrite($fid, $contents);
        fclose($fid);

        return $temp_dir;
    }

    function createGraphVizDump($tree, $temp_dir): string
    {
        $out = "";
        $dot = new Dot($tree, $this, Registry::filesystem()->data());



        $formSubmission = new FormSubmission();
        $vars = $formSubmission->load($_REQUEST['vars']);
        if (isset($temp_dir)) {
            $vars['temp_dir'] = $temp_dir;
        }
        $dot->setSettings($vars);

        $settings = new Settings();
        $settings->saveUserSettings($tree,$dot->settings);
        // Get out DOT file
        $out .= $dot->createDOTDump();
        if (isset($_POST["browser"]) && $_POST["browser"] == "true") {
            $dot->messages[] = I18N::translate('Generated %s individuals and %s family records', sizeof($dot->individuals), sizeof($dot->families));
            $response['messages'] = $dot->messages;
            $response['debug'] = $dot->debug_string;
            $response['dot'] = $out;
            $settings = new Cookie($tree);
            $response['settings'] = json_encode($settings->load([]));
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
     * @param $vars
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