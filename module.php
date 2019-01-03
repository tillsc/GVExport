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

require_once( dirname(__FILE__)."/config.php");
require( dirname(__FILE__)."/utils.php");

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Functions\FunctionsPrint;

/**
 * Main class for GVExport module
 */
class GVExport	extends AbstractModule implements ModuleMenuInterface {

    function __construct() {
        parent::__construct('gvexport');
        $this->directory = WT_MODULES_DIR . $this->getName();
        $this->action = Filter::get('mod_action');
        // register the namespaces
        $loader = new ClassLoader();
        $loader->addPsr4('vendor\\WebtreesModules\\gvexport\\', $this->directory);
        $loader->register();
    }

    public function getName() {
		return 'GVExport';
    }

    public function getTitle() {
    	return 'GVExport';
    }

    public function getDescription() {
        return 'This is the "GVExport" module';
    }

    public function defaultAccessLevel(): int {
		return Auth::PRIV_PRIVATE;
    }

	public function defaultMenuOrder() {
		return 40;
	}

	public function getMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$menu = new Menu($this->getTitle(), 'module.php?mod='.$this->getName().'&amp;mod_action=allinonetree', 'menu-help');
		return $menu;
	}

	/**
	* Entry point for the WT module system.
	*
	* Each module needs to implement a modAction function to actually
	* be able to do stuff.
	* This function handles all of the output, so all of your branching
	* for the module needs to happen in this function.
	*
	*/
    public function modAction($mod_action) {
	    global $WT_TREE;

	    switch($mod_action) {
            case 'allinonetree-run':
                $this->action_runAllInOneTree();
                break;
            case 'allinonetree':
                global $controller;
                $controller = new PageController();
                $controller->pageHeader();
				$this->action_formAllInOneTree();
                break;
            default:
                global $controller;
                $controller=new PageController();
                $controller->pageHeader();
                echo 'Internal error - unknown action:', $mod_action;
        }
    }

    function action_runAllInOneTree(){
        if ( isset( $_REQUEST["vars"]["debug"])) {
            $this->showDOTFile( $_REQUEST['pid']);
        } else {
            $temp_dir = $this->saveDOTFile();
            if ( !empty( $_REQUEST['vars']['otype'])) {
                $this->downloadFile( $temp_dir, $_REQUEST['vars']['otype']);
            }
        }
    }

	/**
	 * Download a DOT file to the user's computer
	 *
	 * @param string $temp_dir
	 * @param string $file_type
 	 */

	function downloadFile( $temp_dir, $file_type) {
        global $GVE_CONFIG;

		$basename = $GVE_CONFIG["filename"] . "." . $GVE_CONFIG["output"][$file_type]["extension"]; // new
		$filename = $temp_dir . "/" . $basename; // new
		if ( !empty( $GVE_CONFIG["output"][$file_type]["exec"])) {
			// Multi-platform operability (by Thomas Ledoux)
			$old_dir = getcwd(); // save the current directory
			chdir($temp_dir); // change to the right directory for generation
			$shell_cmd = $GVE_CONFIG["output"][$file_type]["exec"];
			exec($shell_cmd, $temp, $return_var); // new
			chdir($old_dir); // back to the saved directory
			if ($return_var !== 0) // check correct output generation
			{
				die("Error (return code $return_var) executing command \"$shell_cmd\".<br>Check path and Graphviz functionality!"); // new
			}
		}

		if (!empty($_REQUEST['vars']['disposition']))
		{
			$disposition = "attachment; filename=".$basename;
		}
		else
		{
			$disposition = 'inline';
		}

		header("Content-Description: File Transfer");
		header("Content-Type:" . $GVE_CONFIG["output"][$file_type]["cont_type"]);
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($filename));
		header("Content-Disposition: $disposition"); // new
		readfile( $filename);
	}

	/**
	 * Creates and saves a DOT file
	 *
	 * @return	string	Directory where the file is saved
	 */
	function saveDOTFile()	{
        global $GVE_CONFIG;

		// Make a unique directory to the tmp dir
		$temp_dir = sys_get_temp_dir_my() . "/" . md5(Auth::id());
		if( !is_dir("$temp_dir")) {
			mkdir( "$temp_dir");
		}

		// Create the dump
		$contents = $this->createGraphVizDump( $temp_dir);

		// Put the contents into the file
		$fid = fopen( $temp_dir . "/" . $GVE_CONFIG["filename"] . ".dot","w");
		fwrite($fid,$contents);
		fclose($fid);

		return $temp_dir;
	}

	function showDOTFile()	{

		// Create the dump
		$temp_dir = sys_get_temp_dir_my() . "/" . md5($_SESSION['WEBTREES']["wt_user"]);
		header("Content-Type: text/html; charset=UTF-8");
		$contents = $this->createGraphVizDump( $temp_dir);
		$contents = "<pre>" . htmlspecialchars( $contents, ENT_QUOTES) . "</pre>";
		print $contents;
		//print nl2br( $contents);
	}

	function createGraphVizDump( $temp_dir) {
        require( dirname(__FILE__)."/functions_dot.php");

		$out = "";
		$dot = new Dot;

		$cookieStr = "";
		foreach ($_REQUEST['vars'] as $key => $value)
		    $cookieStr.= "$key=$value|";

		setcookie("GVEUserDefaults", $cookieStr, time() + (3600 * 24 * 365));

		if ( isset( $temp_dir)) {
			$dot->setSettings("temp_dir", $temp_dir);
		}

		if ( isset( $_REQUEST["pid"]) && ($_REQUEST['vars']['indiinc'] != "all")) {
			// INDI id
			if ( !empty( $_REQUEST["other_pids"])) {
				$dot->setSettings("indi", $_REQUEST["pid"] . $_REQUEST["other_pids"]);
				$dot->setSettings("multi_indi", TRUE);
			} else {
				$dot->setSettings("indi", $_REQUEST["pid"]);
				$dot->setSettings("multi_indi", FALSE);
			}
			// Stop PIDs
			if ( !empty( $_REQUEST["other_stop_pids"]) || !empty( $_REQUEST["stop_pid"]) ) {
				$dot->setSettings("stop_pids", $_REQUEST["stop_pid"] . $_REQUEST["other_stop_pids"]);
				$dot->setSettings("stop_proc", TRUE);
			} else {
				$dot->setSettings("stop_proc", FALSE);
			}

			if ( isset( $_REQUEST['vars']['indiance'])) {
				$dot->setIndiSearchMethod( "ance");
			}
			if ( isset( $_REQUEST['vars']['indisibl'])) {
				$dot->setIndiSearchMethod( "sibl");
			}
			if ( isset( $_REQUEST['vars']['indidesc'])) {
				$dot->setIndiSearchMethod( "desc");
			}
			if ( isset( $_REQUEST['vars']['indispou'])) {
				$dot->setIndiSearchMethod( "spou");
			}
			if ( isset( $_REQUEST['vars']['indicous'])) {
				$dot->setIndiSearchMethod( "cous");
			}
			if ( isset( $_REQUEST['vars']['ance_level'])) {
				$dot->setSettings( "ance_level", $_REQUEST["vars"]["ance_level"]);
			} else {
				$dot->setSettings( "ance_level", 0);
			}
			if ( isset( $_REQUEST['vars']['desc_level'])) {
				$dot->setSettings( "desc_level", $_REQUEST["vars"]["desc_level"]);
			} else {
				$dot->setSettings( "desc_level", 0);
			}
		}

		if ( isset( $_REQUEST["vars"]["mclimit"])) {
			$dot->setSettings( "mclimit", $_REQUEST["vars"]["mclimit"]);
		}

		if ( isset( $_REQUEST['vars']['marknv'])) {
			$dot->setSettings( "mark_not_validated", TRUE);
		}

		if ( isset( $_REQUEST['vars']['marknr'])) {
			$dot->setSettings( "mark_not_related", TRUE);
		}

		if ( isset( $_REQUEST['vars']['show_lt_editor'])) {
			$dot->setSettings( "show_lt_editor", TRUE);
		}

		if ( isset( $_REQUEST['vars']['fontsize'])) {
			$dot->setFontSize( $_REQUEST['vars']['fontsize']);
		}

		if ( isset( $_REQUEST['vars']['fontname'])) {
			$dot->setSettings( "fontname", $_REQUEST['vars']['fontname']);
		}

		if ( isset( $_REQUEST['vars']['pagebrk'])) {
			$dot->setSettings( "use_pagesize", $_REQUEST['vars']['psize']);
			$dot->setPageSize( $_REQUEST['vars']['psize']);
		}

		if ( isset( $_REQUEST['vars']['grdir'])) {
			$dot->setSettings( "graph_dir", $_REQUEST['vars']['grdir']);
		}

		// Which data to show
		if ( isset( $_REQUEST['vars']['show_by'])) {
			$dot->setSettings( "show_by", TRUE);
		}
		if ( isset( $_REQUEST['vars']['bd_type'])) {
			$dot->setSettings( "bd_type", $_REQUEST['vars']['bd_type']);
		}
		if ( isset( $_REQUEST['vars']['show_bp'])) {
			$dot->setSettings( "show_bp", TRUE);
		}
		if ( isset( $_REQUEST['vars']['show_dy'])) {
			$dot->setSettings( "show_dy", TRUE);
		}
		if ( isset( $_REQUEST['vars']['dd_type'])) {
			$dot->setSettings( "dd_type", $_REQUEST['vars']['dd_type']);
		}
		if ( isset( $_REQUEST['vars']['show_dp'])) {
			$dot->setSettings( "show_dp", TRUE);
		}
		if ( isset( $_REQUEST['vars']['show_my'])) {
			$dot->setSettings( "show_my", TRUE);
		}
		if ( isset( $_REQUEST['vars']['md_type'])) {
			$dot->setSettings( "md_type", $_REQUEST['vars']['md_type']);
		}
		if ( isset( $_REQUEST['vars']['show_mp'])) {
			$dot->setSettings( "show_mp", TRUE);
		}
		if ( isset( $_REQUEST['vars']['show_pid'])) {
			$dot->setSettings( "show_pid", TRUE);
		}
		if ( isset( $_REQUEST['vars']['show_fid'])) {
			$dot->setSettings( "show_fid", TRUE);
		}

		if ( isset( $_REQUEST['vars']['show_url'])) {
			$dot->setSettings( "show_url", TRUE);
		}

		if ( isset( $_REQUEST['vars']['use_abbr_place'])) {
			$dot->setSettings( "use_abbr_place", TRUE);
		}

		if ( !empty( $_REQUEST['vars']['media_dir'])) {
			$dot->setSettings( "media_dir", $_REQUEST['vars']['media_dir']);
		} else {
			$dot->setSettings( "media_dir", FALSE);
		}

		if ( isset( $_REQUEST['vars']['debug'])) {
			$dot->setSettings( "debug", TRUE);
		}


		// Set custom colors
		if ( $_REQUEST["vars"]["colorm"] == "custom") {
			$dot->setColor("colorm", $_REQUEST["colorm_custom_var"]);
		}
		if ( $_REQUEST["vars"]["colorf"] == "custom") {
			$dot->setColor("colorf", $_REQUEST["colorf_custom_var"]);
		}
		if ( $_REQUEST["vars"]["coloru"] == "custom") {
			$dot->setColor("coloru", $_REQUEST["coloru_custom_var"]);
		}
		if ( $_REQUEST["vars"]["colorfam"] == "custom") {
			$dot->setColor("colorfam", $_REQUEST["colorfam_custom_var"]);
		}

		// Settings
		if ( !empty( $_REQUEST['vars']['diagtype'])) {
			$dot->setSettings( "diagram_type", $_REQUEST['vars']['diagtype']);
			$dot->setSettings( "diagram_type_combined_with_photo", !empty( $_REQUEST['vars']['diagtypeCombinedWithPhoto'])); #ESL!!! 20090213
		}
		if ( !empty( $_REQUEST['vars']['no_fams'])) {
			$dot->setSettings( "no_fams", $_REQUEST['vars']['no_fams']);
		}

		if ( isset( $_REQUEST['vars']['dpi'])) {
			$dot->setSettings( "dpi", $_REQUEST['vars']['dpi']);
		}
		if ( isset( $_REQUEST['vars']['ranksep'])) {
			$dot->setSettings( "ranksep", $_REQUEST['vars']['ranksep']);
		}
		if ( isset( $_REQUEST['vars']['nodesep'])) {
			$dot->setSettings( "nodesep", $_REQUEST['vars']['nodesep']);
		}

		$out .= $dot->getDOTDump();
		return $out;
	}

	/**
	 * Shows the form for All-in-One Tree
	 *
	 */
	function action_formAllinOneTree() {
        global $GVE_CONFIG;

		$userDefaultVars = array(//Defaults (this cloud be defined in the config?)
		    "grdir" => $GVE_CONFIG["default_direction"],
		    "mclimit" => $GVE_CONFIG["mclimit"][$GVE_CONFIG["default_mclimit"]],
		    "psize" => $GVE_CONFIG["default_pagesize"],
		    "indiinc" => "all",
		    "diagtype" => "simple",
		    "diagtypeCombinedWithPhoto" => FALSE, #ESL!!! 20090213
		    "use_abbr_place" => ($GVE_CONFIG['settings']['use_abbr_place'] ? "use_abbr_place" : ""),
		    "show_by" => "show_by",
		    "show_bp" => "show_bp",
		    "show_dy" => "show_dy",
		    "show_dp" => "show_dp",
		    "show_my" => "show_my",
		    "show_mp" => "show_mp",
			"tree_type" => "tree_type",
		    "debug" => ($GVE_CONFIG['debug'] ? "debug" : ""),
			"dpi" => $GVE_CONFIG["settings"]["dpi"],
			"ranksep" => $GVE_CONFIG["settings"]["ranksep"],
			"nodesep" => $GVE_CONFIG["settings"]["nodesep"],
		);
		if (isset($_COOKIE["GVEUserDefaults"]) and $_COOKIE["GVEUserDefaults"] != "") {
		  foreach (explode("|", $_COOKIE["GVEUserDefaults"]) as $s) {
		    $arr = explode("=", $s);
		    if (count($arr) == 2) {
			$userDefaultVars[$arr[0]] = $arr[1];
		    }
		  }
		}

		global $controller;
		$out=$js="";
		// JQuery code
		//$js .= "$(document).ready(function(){\n";
		//$js .= "  $(\"#test_button\").toggle(\n    function(){ $(\"#div_tohide\").hide('slow');},\n    function(){ $(\"#div_tohide\").show('slow');}\n  );\n";
		//$js .= "  $(\"#tab-1_btn\").toggle(\n    function(){ $(\"#tab-1\").hide('fast');},\n    function(){ $(\"#tab-1\").show('fast');}\n  );\n";
		//$js .= "  $(\"#tab-2_btn\").toggle(\n    function(){ $(\"#tab-2\").show('fast');},\n    function(){ $(\"#tab-2\").hide('fast');}\n  );\n";
		//$js .= "  $(\"#tab-3_btn\").toggle(\n    function(){ $(\"#tab-3\").show('fast');},\n    function(){ $(\"#tab-3\").hide('fast');}\n  );\n";
		//$js .= "  $(\"#tab-adv_btn\").toggle(\n    function(){ $(\"#tab-adv\").show('fast');},\n    function(){ $(\"#tab-adv\").hide('fast');}\n  );\n";
		//$js .= "  $(\"#test_button\").toggle(\n    function(){ $(\"#div_tohide\").hide('slow');},\n    function(){ $(\"#div_tohide\").show('slow');}\n  );\n";
		//$js .= "});";
		// JavaScript
		$js .= "function gve_enablecustomcolor(cn) {\n document.getElementById(cn).disabled=false;\n document.getElementById(cn).style.backgroundColor=document.getElementById(cn).value;\n return false;\n }\n";
		$js .= "var pastefield;\n function paste_id(value) {\n pastefield.value = value;\n }\n";
        $controller->addInlineJavascript($js);

		// Form
		$out .= "<form name=\"setup_gvexport_allinontree\" method=\"post\" target=\"_blank\" action=\"module.php?mod=".$this->getName()."&mod_action=allinonetree-run\">";
		$out .= "<table class=\"width80 center\">";
		$out .= "<tr><td class=\"topbottombar\">" . "All-in-one Tree" . "</td></tr>";

		// --- Output settings ---
		$out .= "<tr><td class=\"topbottombar\"><div align=\"left\"><a id=\"tab-1_btn\" href=\"#\">" . "Output Settings" . "</a></div></td></tr>";
		$out .= "<tr><td>";
		$out .= "<div id=\"tab-1\">";
		$out .= "<table class=\"center width100\" style=\"text-align: left;\">";

		// Tree type
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Tree Type" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"radio\" name=\"vars[treetype]\" id=\"treetype_var\" value=\"aio\"".((!isset($userDefaultVars["treetype"]) or $userDefaultVars["treetype"] == "aio") ? " checked=\"checked\"" : "")." />" . "GraphViz All-in-One" . "<br/>";
		//$out.="<input type=\"radio\" name=\"vars[treetype]\" id=\"treetype_var\" value=\"desc\"".((isset($userDefaultVars["treetype"]) and $userDefaultVars["treetype"] == "desc") ? " checked=\"checked\"" : "")." />" . "PDF Descendancy";
		$out.="</td>\n";
		$out.="</tr>\n";

		// Output file type
		$out .= "<tr><td rowspan=\"2\" class=\"descriptionbox wrap\">" ."Output File Type" . "<br/>" . "Choose DOT if you don't have GraphViz installed on server." . "</td>";
		$out .= "<td style=\"text-align: left;\" class=\"optionbox\">";
//		if ( !empty( $GVE_CONFIG["graphviz_bin"])) {
                    $out .= "<select name=\"vars[otype]\" id=\"otype_var\">";

                    $otypes = array();

                    #ESL!!! 20090213 You can add or comment some formats in the config
                    foreach ($GVE_CONFIG["output"] as $fmt => $val) {
                            if (isset($GVE_CONFIG["output"][$fmt]["label"]) and
                                isset($GVE_CONFIG["output"][$fmt]["extension"])) {
                                    $lbl = $GVE_CONFIG["output"][$fmt]["label"];
                                    $ext = $GVE_CONFIG["output"][$fmt]["extension"];
                                    $otypes[$ext] = $lbl;
                            }
                    }

                    foreach($otypes as $otvalue => $otlabel) {
                        $out .= "<option value=\"$otvalue\"".((isset($userDefaultVars["otype"]) and $userDefaultVars["otype"] == $otvalue) ? " selected=\"selected\"" : "").">$otlabel</option>";
                    }

                    $out.="</select>";
//		}

		$out.="</td></tr>";

		// Disposition type
		$out.="<tr><td style=\"text-align: left;\" class=\"optionbox\">";
		$out.="<input type=\"checkbox\" name=\"vars[disposition]\" id=\"disposition_var\" value=\"disposition\"".((isset($userDefaultVars["disposition"]) and $userDefaultVars["disposition"] == "disposition") ? " checked=\"checked\"" : "")." />" . "Generate a file for download" . " "; // new
		$out.="</td></tr>";

		// Use page breaking
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Use Page Break" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"checkbox\" name=\"vars[pagebrk]\" id=\"pagebrk_var\" value=\"pagebrk\" ".((isset($userDefaultVars["pagebrk"]) and $userDefaultVars["pagebrk"] == "pagebrk") ? " checked=\"checked\"" : "")."/> ";
		$out.="<select name=\"vars[psize]\" id=\"psize_var\">";
		foreach ( $GVE_CONFIG["pagesize"] as $pagesize_n => $pagesize_data) {
			$out.="<option value=\"" . $pagesize_n . "\"".((isset($userDefaultVars["psize"]) and $userDefaultVars["psize"] == $pagesize_n) ? " selected=\"selected\"" : "").">" . $pagesize_n . "</option>";
		}
		$out.="</select></td></tr>";

		// Graph direction
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Graph Direction" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<select name=\"vars[grdir]\" id=\"grdir_var\">";
		foreach ( $GVE_CONFIG["direction"] as $grdir_n => $grdir_data) {
			$out.="<option value=\"" . $grdir_n . "\"".((isset($userDefaultVars["grdir"]) and $userDefaultVars["grdir"] == $grdir_n) ? " selected=\"selected\"" : "").">" . $grdir_n . "</option>";
		}
		$out.="</select></td></tr>";

		// mclimit settings
		$out.="<tr><td class=\"descriptionbox wrap\">" . "\"MCLIMIT\" setting, a.k.a. number of iterations which helps to reduce the crossings on the graph.<br />This can be really slow (up to 10..15x compared to default (20) setting)" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<select name=\"vars[mclimit]\" id=\"mclimit_var\">";
		foreach ( $GVE_CONFIG["mclimit"] as $mclimit_n => $mclimit_data) {
			$out.="<option value=\"" . $mclimit_data . "\"".((isset($userDefaultVars["mclimit"]) and $userDefaultVars["mclimit"] == $mclimit_data) ? " selected=\"selected\"" : "").">" .$mclimit_data . "</option>";
		}
		$out .= "</select></td></tr>";

		// Graph look settings
		$out .= "<tr><td class=\"descriptionbox wrap\">" . "Graph Look" . "</td>";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		$out .= "<input type=\"text\" size=\"10\" name=\"vars[dpi]\" id=\"dpi\" value=\"".(isset($userDefaultVars["dpi"]) ? $userDefaultVars["dpi"] : $GVE_CONFIG["settings"]["dpi"])."\" /> " . "dpi". "<br />";
		$out .= "ranksep: " . "<input type=\"text\" size=\"10\" name=\"vars[ranksep]\" id=\"ranksep\" value=\"".(isset($userDefaultVars["ranksep"]) ? $userDefaultVars["ranksep"] : $GVE_CONFIG["settings"]["ranksep"])."\" /> " . "&nbsp;";
		$out .= "nodesep: " . "<input type=\"text\" size=\"10\" name=\"vars[nodesep]\" id=\"nodesep\" value=\"".(isset($userDefaultVars["nodesep"]) ? $userDefaultVars["nodesep"] : $GVE_CONFIG["settings"]["nodesep"])."\" /> ";
		$out .= "</td></tr>";

		$out .= "</table>";
		$out .= "</div>";
		$out .= "</td></tr>";

		// --- Diagram preferences ---
		$out .= "<tr><td class=\"topbottombar\"><div align=\"left\"><a id=\"tab-2_btn\" href=\"#\">" . "Diagram preferences" . "</a></div></td></tr>\n";
		$out .= "<tr><td>";
		$out .= "<div id=\"tab-2\">";
		$out .= "<table class=\"center width100\" style=\"text-align: left;\">";

		// Individuals to be included
		$out .= "<tr><td rowspan=\"2\" class=\"descriptionbox wrap\">" ."Individuals Included" . "</td>\n";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		// Everyone
		$out .= "<input type=\"radio\" name=\"vars[indiinc]\" id=\"indiinc_var\" value=\"all\"".((isset($userDefaultVars["indiinc"]) and $userDefaultVars["indiinc"] == "all") ? " checked=\"checked\"" : "")." />" . "Everyone";
        $out .= "</td>\n";
		$out .= "</tr>\n";
		$out .= "<tr>";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		// Anyone related to persons
		$out .= "<input type=\"radio\" name=\"vars[indiinc]\" id=\"indiinc_var\" value=\"indi\"".((isset($userDefaultVars["indiinc"]) and $userDefaultVars["indiinc"] == "indi") ? " checked=\"checked\"" : "")." />" . "Related To" . " ";
		// Check if PID was set already, if not then use the PGV's user's default PID
		if (isset($_REQUEST['pid'])) {
			$pid = $_REQUEST['pid'];
		} else {
			$pid = "I1";//$controller->getSignificantIndividual()->getXref();
		}
		//$out.="<input type=\"text\" size=\"10\" name=\"pid\" id=\"pid\" value=\"".check_rootid(isset($userDefaultVars["pid"]) ? $userDefaultVars["pid"] : "")."\"/>";
		$out .= "<input type=\"text\" size=\"10\" name=\"pid\" id=\"pid\" value=\"" . $pid . "\"/>";
        $out .= FunctionsPrint::printFindIndividualLink("pid");
		if (isset($_REQUEST['other_pids'])) {
			$other_pids = $_REQUEST['other_pids'];
		} else {
			$other_pids = "";
		}
		$out .= "&nbsp;<input type=\"button\" value=\">>>\" onclick=\"document.setup_gvexport_allinontree.other_pids.value=document.setup_gvexport_allinontree.other_pids.value+','+document.setup_gvexport_allinontree.pid.value;\" /> ";
		$out .= "<input type=\"text\" size=\"30\" name=\"other_pids\" id=\"other_pids\" value=\"" . $other_pids . "\" />";
		$out .= "<br/>";

		// Stop tree processing on the indis
		$out .= "Stop tree processing on INDIs:" . "&nbsp;";
		if (isset($_REQUEST['stop_pid'])) {
			$stop_pid = $_REQUEST['stop_pid'];
		} else {
			$stop_pid = "";
		}
		$out .= "<input type=\"text\" size=\"10\" name=\"stop_pid\" id=\"stop_pid\" value=\"" . $stop_pid . "\"/>";
		$out .= FunctionsPrint::printFindIndividualLink("stop_pid");
		if (isset($_REQUEST['other_stop_pids'])) {
			$other_stop_pids = $_REQUEST['other_stop_pids'];
		} else {
			$other_stop_pids = "";
		}
		$out .= "&nbsp;<input type=\"button\" value=\">>>\" onclick=\"document.setup_gvexport_allinontree.other_stop_pids.value=document.setup_gvexport_allinontree.other_stop_pids.value+','+document.setup_gvexport_allinontree.stop_pid.value;\" /> ";
		$out .= "<input type=\"text\" size=\"30\" name=\"other_stop_pids\" id=\"other_stop_pids\" value=\"" . $other_stop_pids . "\" />";
		$out .= "<br/>";

		$out .= "<input type=\"checkbox\" name=\"vars[indiance]\" id=\"indiance_var\" value=\"ance\"".((isset($userDefaultVars["indiance"]) and $userDefaultVars["indiance"] == "ance") ? " checked=\"checked\"" : "")." onclick=\"if (this.checked==true) { document.getElementById('ance_level_var').disabled=false; } else { document.getElementById('ance_level_var').disabled=true; }\"/>" . "Include Ancestors";
		$out .= " (" . "Max Levels" . " : " . "<input type=\"text\" size=\"2\" name=\"vars[ance_level]\" id=\"ance_level_var\" value=\"".(isset($userDefaultVars["ance_level"]) ? $userDefaultVars["ance_level"] : $GVE_CONFIG["settings"]["ance_level"])."\"".((isset($userDefaultVars["indiance"]) and $userDefaultVars["indiance"] == "ance") ? "" : " disabled=\"disabled\"")." />" . ")<br/>";
		$out .= "<input type=\"checkbox\" name=\"vars[indisibl]\" id=\"indisibl_var\" value=\"sibl\"".((isset($userDefaultVars["indisibl"]) and $userDefaultVars["indisibl"] == "sibl") ? " checked=\"checked\"" : "")." onclick=\"if (this.checked==true) { document.getElementById('indicous_var').disabled=false; } else { document.getElementById('indicous_var').disabled=true; }\" />" . "Include Siblings" . " ";
		$out .= "<input type=\"checkbox\" name=\"vars[indicous]\" id=\"indicous_var\" value=\"cous\"".((isset($userDefaultVars["indicous"]) and $userDefaultVars["indicous"] == "cous") ? " checked=\"checked\"" : "")." ".((isset($userDefaultVars["indisibl"]) and $userDefaultVars["indisibl"] == "sibl") ? "" : " disabled=\"disabled\"")."/>" . "Include Cousins" . "<br/>";
		$out .= "<input type=\"checkbox\" name=\"vars[indidesc]\" id=\"indidesc_var\" value=\"desc\"".((isset($userDefaultVars["indidesc"]) and $userDefaultVars["indidesc"] == "desc") ? " checked=\"checked\"" : "")." onclick=\"if (this.checked==true) { document.getElementById('desc_level_var').disabled=false; } else { document.getElementById('desc_level_var').disabled=true; }\"/ />" . "Include Descendants";
		$out .= " (" . "Max Levels" . " : " . "<input type=\"text\" size=\"2\" name=\"vars[desc_level]\" id=\"desc_level_var\" value=\"".(isset($userDefaultVars["desc_level"]) ? $userDefaultVars["desc_level"] : $GVE_CONFIG["settings"]["desc_level"])."\"".((isset($userDefaultVars["indidesc"]) and $userDefaultVars["indidesc"] == "desc") ? "" : " disabled=\"disabled\"")." />" . ")<br/>";
		$out .= "<input type=\"checkbox\" name=\"vars[indispou]\" id=\"indispou_var\" value=\"spou\"".((isset($userDefaultVars["indispou"]) and $userDefaultVars["indispou"] == "spou") ? " checked=\"checked\"" : "")." />" . "Include Spouses" . "<br/>";
		$out .= "<input type=\"checkbox\" name=\"vars[marknr]\" id=\"marknr_var\" value=\"marknr\"".((isset($userDefaultVars["marknr"]) and $userDefaultVars["marknr"] == "marknr") ? " checked=\"checked\"" : "")." />" . "Mark not blood-related people with different color" . " ";
		$out .= "</td>\n";
		$out .= "</tr>\n";

		// Mark not validated data & Show last editor of the data
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Mark those individuals which facts are not validated yet" . "</td>\n";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"checkbox\" name=\"vars[marknv]\" id=\"marknv_var\" value=\"marknv\"".((isset($userDefaultVars["marknv"]) and $userDefaultVars["marknv"] == "marknv") ? " checked=\"checked\"" : "")." onclick=\"if (this.checked==true) { document.getElementById('show_lt_editor_var').disabled=false; } else { document.getElementById('show_lt_editor_var').disabled=true; }\"/>&nbsp;";
		$out .= "(" . "Show last editor's username" . ": " . "<input type=\"checkbox\" name=\"vars[show_lt_editor]\" id=\"show_lt_editor_var\" value=\"show_lt_editor\"".((isset($userDefaultVars["show_lt_editor"]) and $userDefaultVars["show_lt_editor"] == "show_lt_editor") ? " checked=\"checked\"" : "")." ".((isset($userDefaultVars["marknv"]) and $userDefaultVars["marknv"] == "marknv") ? "" : " disabled=\"disabled\"")." />" . ")";
    		$out.="</td>\n";
		$out.="</tr>\n";

		// Show URLs
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Add URL to individuals and families" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"checkbox\" name=\"vars[show_url]\" id=\"show_url_var\" value=\"show_url\"".((isset($userDefaultVars["show_url"]) and $userDefaultVars["show_url"] == "show_url") ? " checked=\"checked\"" : "")." />";
		$out.="</td></tr>";

		// Use abbrviated/full placenames
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Use abbreviated placenames" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"checkbox\" name=\"vars[use_abbr_place]\" id=\"use_abbr_place_var\" value=\"use_abbr_place\"".((isset($userDefaultVars["use_abbr_place"]) and $userDefaultVars["use_abbr_place"] == "use_abbr_place") ? " checked=\"checked\"" : "")." />";
		$out.="</td></tr>";

		// Indi container settings
		$out.="<tr>\n<td rowspan=\"3\" class=\"descriptionbox wrap\">" ."Personal data to be shown" . "</td>\n";
		// Indi ID
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"checkbox\" name=\"vars[show_pid]\" id=\"show_pid_var\" value=\"show_pid\"".((isset($userDefaultVars["show_pid"]) and $userDefaultVars["show_pid"] == "show_pid") ? " checked=\"checked\"" : "")." /> " . "Individual ID" . "<br/>";
		$out.="</td>\n</tr>\n";
		// Birth data
		$out.="<tr>\n";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">"  . "Birth" . "<br/>\n";
		$out.="<input type=\"checkbox\" name=\"vars[show_by]\" id=\"show_by_var\" value=\"show_by\"".((isset($userDefaultVars["show_by"]) and $userDefaultVars["show_by"] == "show_by") ? " checked=\"checked\"" : "")."/> " . "Date" . " ";
		$out.="<input type=\"radio\" name=\"vars[bd_type]\" id=\"bd_type_var\" value=\"y\"".((isset($userDefaultVars["bd_type"]) and $userDefaultVars["bd_type"] == "gedcom") ? "" : " checked=\"checked\"")."/> " . "Year" . " ";
		$out.="<input type=\"radio\" name=\"vars[bd_type]\" id=\"bd_type_var\" value=\"gedcom\"".((isset($userDefaultVars["bd_type"]) and $userDefaultVars["bd_type"] == "gedcom") ? " checked=\"checked\"" : "")." /> " . "Full Date" . "<br/>";
		$out.="<input type=\"checkbox\" name=\"vars[show_bp]\" id=\"show_bp_var\" value=\"show_bp\"".((isset($userDefaultVars["show_bp"]) and $userDefaultVars["show_bp"] == "show_bp") ? " checked=\"checked\"" : "")." /> " . "Place" . "<br/>";
		$out.="</td>\n</tr>\n";
		// Death data
		$out.="<tr>\n";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">"  ."Death" . "<br/>\n";
		$out.="<input type=\"checkbox\" name=\"vars[show_dy]\" id=\"show_dy_var\" value=\"show_dy\"".((isset($userDefaultVars["show_dy"]) and $userDefaultVars["show_dy"] == "show_dy") ? " checked=\"checked\"" : "")."/> " . "Date" . " ";
		$out.="<input type=\"radio\" name=\"vars[dd_type]\" id=\"dd_type_var\" value=\"y\"".((isset($userDefaultVars["dd_type"]) and $userDefaultVars["dd_type"] == "gedcom") ? "" : " checked=\"checked\"")."/> " . "Year" . " ";
		$out.="<input type=\"radio\" name=\"vars[dd_type]\" id=\"dd_type_var\" value=\"gedcom\"".((isset($userDefaultVars["dd_type"]) and $userDefaultVars["dd_type"] == "gedcom") ? " checked=\"checked\"" : "")." /> " . "Full Date" . "<br/>";
		$out.="<input type=\"checkbox\" name=\"vars[show_dp]\" id=\"show_dp_var\" value=\"show_dp\"".((isset($userDefaultVars["show_dp"]) and $userDefaultVars["show_dp"] == "show_dp") ? " checked=\"checked\"" : "")." /> " . "Place";
		$out.="</td></tr>\n";

		// Marriage container settings
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Marriage data to be shown" . "</td>";
		// Family ID
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.="<input type=\"checkbox\" name=\"vars[show_fid]\" id=\"show_fid_var\" value=\"show_fid\"".((isset($userDefaultVars["show_fid"]) and $userDefaultVars["show_fid"] == "show_fid") ? " checked=\"checked\"" : "")." /> " . "Family ID" . "<br/>";
		// Mariage data
		$out.= "Marriage" . "<br/><input type=\"checkbox\" name=\"vars[show_my]\" id=\"show_my_var\" value=\"show_my\"".((isset($userDefaultVars["show_my"]) and $userDefaultVars["show_my"] == "show_my") ? " checked=\"checked\"" : "")." /> " . "Date" . " ";
		$out.="<input type=\"radio\" name=\"vars[md_type]\" id=\"md_type_var\" value=\"y\"".((isset($userDefaultVars["md_type"]) and $userDefaultVars["md_type"] == "gedcom") ? "" : " checked=\"checked\"")."/> " . "Year" . " ";
		$out.="<input type=\"radio\" name=\"vars[md_type]\" id=\"md_type_var\" value=\"gedcom\"".((isset($userDefaultVars["md_type"]) and $userDefaultVars["md_type"] == "gedcom") ? " checked=\"checked\"" : "")." /> " . "Full Date" . "<br/>";
		$out.=" <input type=\"checkbox\" name=\"vars[show_mp]\" id=\"show_mp_var\" value=\"show_mp\"".((isset($userDefaultVars["show_mp"]) and $userDefaultVars["show_mp"] == "show_mp") ? " checked=\"checked\"" : "")." /> " . "Place";
		$out.="</td></tr>";

		$out .= "</table>";
		$out .= "</div>";
		$out .= "</td></tr>";


		// --- Appearance ---
		$out .= "<tr><td class=\"topbottombar\"><div align=\"left\"><a id=\"tab-3_btn\" href=\"#\">" . "Appearance" . "</a></div></td></tr>\n";
		$out .= "<tr><td>";
		$out .= "<div id=\"tab-3\">";
		$out .= "<table class=\"center width100\" style=\"text-align: left;\">";

		// Diagram type
		$out.="<tr><td class=\"descriptionbox wrap\">" . "Diagram Type" . "</td>";
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.='<input type="radio" name="vars[diagtype]" id="diagtype_var" value="simple"'.((isset($userDefaultVars["diagtype"]) and $userDefaultVars["diagtype"] == "simple") ? " checked=\"checked\"" : "").' />'. "Simple" . "<br/>";
		$out.='<input type="radio" name="vars[diagtype]" id="diagtype_var" value="decorated"'.((isset($userDefaultVars["diagtype"]) and $userDefaultVars["diagtype"] == "decorated") ? " checked=\"checked\"" : "").' />'. "Decorated" . "<br/>";
#		$out.='<input type="radio" name="vars[diagtype]" id="diagtype_var" value="deco-photo"'.((isset($userDefaultVars["diagtype"]) and $userDefaultVars["diagtype"] == "deco-photo") ? " checked=\"checked\"" : "").' />'. $pgv_lang["diagram_deco-photo"] . "<br/>"; #ESL!!! 20090213
		$out.='<input type="radio" name="vars[diagtype]" id="diagtype_var" value="combined"'.((isset($userDefaultVars["diagtype"]) and $userDefaultVars["diagtype"] == "combined") ? " checked=\"checked\"" : "").' />'. "Combined";
		$out.="<br/>"; #ESL!!! 20090213
		$out.="<input type=\"checkbox\" name=\"vars[diagtypeCombinedWithPhoto]\" id=\"diagtypeCombinedWithPhoto_var\" value=\"diagtypeCombinedWithPhoto\"".((isset($userDefaultVars["diagtypeCombinedWithPhoto"]) and $userDefaultVars["diagtypeCombinedWithPhoto"] == "diagtypeCombinedWithPhoto") ? " checked=\"checked\"" : "") . " />" . "Add photos (Only Decorated or Combined)"; #ESL!!! 20090213
		$out.="<br/>"; #ESL!!! 20090213
		$out.="<input type=\"checkbox\" name=\"vars[no_fams]\" id=\"no_fams_var\" value=\"no_fams\"".((isset($userDefaultVars["no_fams"]) and $userDefaultVars["no_fams"] == "no_fams") ? " checked=\"checked\"" : "") . " />" . "No family containers, just individuals";
		$out.='</td></tr>';

		// Font name
		$out .= "<tr><td class=\"descriptionbox wrap\">" . "Font Name" . "</td>";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		$out .= "<input type=\"text\" name=\"vars[fontname]\" id=\"fontname_var\" value=\"" . $GVE_CONFIG["default_fontname"] ."\" />";
		$out .= "</td></tr>";

		// Font size
		$out .= "<tr><td class=\"descriptionbox wrap\">" . "Font Size" . "</td>";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		$out .= "<input type=\"text\" size=\"2\" name=\"vars[fontsize]\" id=\"fontsize_var\" value=\"".(isset($userDefaultVars["fontsize"]) ? $userDefaultVars["fontsize"] : $GVE_CONFIG["dot"]["fontsize"])."\" />";
		$out .= "</td></tr>";

		// Custom colors
		$out.='<tr><td class="descriptionbox wrap">' . "Color code of male individuals" . '</td>';
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.='<input type="radio" name="vars[colorm]" id="colorm_var" value="default" onclick="document.setup_gvexport_allinontree.colorm_custom_var.disabled=true;"'.((isset($userDefaultVars['colorm']) and $userDefaultVars['colorm'] == "custom") ? '' : ' checked="checked"').' />';
		$out.='<input type="text" name="colorm_default_var" id="colorm_default_var" value="' . "Default" . '" readonly="readonly" style="background: '.$GVE_CONFIG['dot']['colorm'].';"/>';
		$out.='<input type="radio" name="vars[colorm]" id="colorm_var" value="custom" onclick="gve_enablecustomcolor(\'colorm_custom_var\');"'.((isset($userDefaultVars['colorm']) and $userDefaultVars['colorm'] == "custom") ? ' checked="checked"' : '').' />';
		$defcustcol = isset($userDefaultVars['colorm_custom']) ? $userDefaultVars['colorm_custom'] : $GVE_CONFIG["dot"]["colorm"];
		$out.="<input type=\"text\" name=\"colorm_custom_var\" id=\"colorm_custom_var\" value=\"$defcustcol\" style=\"background-color: $defcustcol\"".((isset($userDefaultVars['colorm']) and $userDefaultVars['colorm'] == "custom") ? '' : ' disabled="disabled"')." onblur=\"document.setup_gvexport_allinontree.colorm_custom_var.style.backgroundColor=document.setup_gvexport_allinontree.colorm_custom_var.value;\" />";
		$out.='</td></tr>';
		$out.='<tr><td class="descriptionbox wrap">' . "Color code of female individuals" . '</td>';
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.='<input type="radio" name="vars[colorf]" id="colorf_var" value="default" onclick="document.setup_gvexport_allinontree.colorf_custom_var.disabled=true;"'.((isset($userDefaultVars['colorf']) and $userDefaultVars['colorf'] == "custom") ? '' : ' checked="checked"').' />';
		$out.='<input type="text" name="colorf_default_var" id="colorf_default_var" value="' . "Default" . '" readonly="readonly" style="background: '.$GVE_CONFIG['dot']['colorf'].';"/>';
		$out.='<input type="radio" name="vars[colorf]" id="colorf_var" value="custom" onclick="gve_enablecustomcolor(\'colorf_custom_var\');"'.((isset($userDefaultVars['colorf']) and $userDefaultVars['colorf'] == "custom") ? ' checked="checked"' : '').' />';
		$defcustcol = isset($userDefaultVars['colorf_custom']) ? $userDefaultVars['colorf_custom'] : $GVE_CONFIG["dot"]["colorf"];
		$out.="<input type=\"text\" name=\"colorf_custom_var\" id=\"colorf_custom_var\" value=\"$defcustcol\" style=\"background-color: $defcustcol\"".((isset($userDefaultVars['colorf']) and $userDefaultVars['colorf'] == "custom") ? '' : ' disabled="disabled"')." onblur=\"document.setup_gvexport_allinontree.colorf_custom_var.style.backgroundColor=document.setup_gvexport_allinontree.colorf_custom_var.value;\" />";
		$out.='</td></tr>';
		$out.='<tr><td class="descriptionbox wrap">' . "Color code of unknown individuals" . '</td>';
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.='<input type="radio" name="vars[coloru]" id="coloru_var" value="default" onclick="document.setup_gvexport_allinontree.coloru_custom_var.disabled=true;"'.((isset($userDefaultVars['coloru']) and $userDefaultVars['coloru'] == "custom") ? '' : ' checked="checked"').' />';
		$out.='<input type="text" name="coloru_default_var" id="coloru_default_var" value="' . "Default" . '" readonly="readonly" style="background: '.$GVE_CONFIG['dot']['coloru'].';"/>';
		$out.='<input type="radio" name="vars[coloru]" id="coloru_var" value="custom" onclick="gve_enablecustomcolor(\'coloru_custom_var\');"'.((isset($userDefaultVars['coloru']) and $userDefaultVars['coloru'] == "custom") ? ' checked="checked"' : '').' />';
		$defcustcol = isset($userDefaultVars['coloru_custom']) ? $userDefaultVars['coloru_custom'] : $GVE_CONFIG["dot"]["coloru"];
		$out.="<input type=\"text\" name=\"coloru_custom_var\" id=\"coloru_custom_var\" value=\"$defcustcol\" style=\"background-color: $defcustcol\"".((isset($userDefaultVars['coloru']) and $userDefaultVars['coloru'] == "custom") ? '' : ' disabled="disabled"')." onblur=\"document.setup_gvexport_allinontree.coloru_custom_var.style.backgroundColor=document.setup_gvexport_allinontree.coloru_custom_var.value;\" />";
		$out.='</td></tr>';
		$out.='<tr><td class="descriptionbox wrap">' . "Color code of families" . '</td>';
		$out.="<td class=\"optionbox\" style=\"text-align: left;\">";
		$out.='<input type="radio" name="vars[colorfam]" id="colorfam_var" value="default" onclick="document.setup_gvexport_allinontree.colorfam_custom_var.disabled=true;"'.((isset($userDefaultVars['colorfam']) and $userDefaultVars['colorfam'] == "custom") ? '' : ' checked="checked"').' />';
		$out.='<input type="text" name="colorfam_default_var" id="colorfam_default_var" value="' . "Default" . '" readonly="readonly" style="background: '.$GVE_CONFIG['dot']['colorfam'].';"/>';
		$out.='<input type="radio" name="vars[colorfam]" id="colorfam_var" value="custom" onclick="gve_enablecustomcolor(\'colorfam_custom_var\');"'.((isset($userDefaultVars['colorfam']) and $userDefaultVars['colorfam'] == "custom") ? ' checked="checked"' : '').' />';
		$defcustcol = isset($userDefaultVars['colorfam_custom']) ? $userDefaultVars['colorfam_custom'] : $GVE_CONFIG["dot"]["colorfam"];
		$out.="<input type=\"text\" name=\"colorfam_custom_var\" id=\"colorfam_custom_var\" value=\"$defcustcol\" style=\"background-color: $defcustcol\"".((isset($userDefaultVars['colorfam']) and $userDefaultVars['colorfam'] == "custom") ? '' : ' disabled="disabled"')." onblur=\"document.setup_gvexport_allinontree.colorfam_custom_var.style.backgroundColor=document.setup_gvexport_allinontree.colorfam_custom_var.value;\" />";
		$out.='</td></tr>';

		$out .= "</table>";
		$out .= "</div>";
		$out .= "</td></tr>";

		// --- Advanced settings ---
		$out .= "<tr><td class=\"topbottombar\"><div align=\"left\"><a id=\"tab-adv_btn\" href=\"#\">" . "Advanced Settings" . "</a></div></td></tr>\n";
		$out .= "<tr><td>";
		$out .= "<div id=\"tab-adv\">";
		$out .= "<table class=\"center width100\" style=\"text-align: left;\">";

		// Debug mode
		$out .= "<tr><td class=\"descriptionbox wrap\">" . "Debug Mode" . "</td>";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		$out .= "<input type=\"checkbox\" name=\"vars[debug]\" id=\"debug_var\" value=\"debug\"".((isset($userDefaultVars["debug"]) and $userDefaultVars["debug"] == "debug") ? " checked=\"checked\"" : "")." /> " . "DOT file & other debug info will be dumped on screen";
		$out .= "</td></tr>";

		// Custom media directory
		$out .= "<tr><td class=\"descriptionbox wrap\">" . "Custom media directory" . "</td>";
		$out .= "<td class=\"optionbox\" style=\"text-align: left;\">";
		if ( $GVE_CONFIG["settings"]["media_dir"] === FALSE) {
			$def_media_dir = "";
		} else {
			$def_media_dir = $GVE_CONFIG["settings"]["media_dir"];
		}
		$out .= "<input type=\"text\" name=\"vars[media_dir]\" id=\"media_dir_var\" value=\"".(isset($userDefaultVars["media_dir"]) ? $userDefaultVars["media_dir"] : $def_media_dir)."\" /> " . "The \"/media/thumbs\" subdir will be added automatically by PGV.";
		$out .= "</td></tr>";


		$out .= "</table>";
		$out .= "</div>";
		$out .= "</td></tr>";

		// --- Buttons at the end of form ---
		$out .= "<tr><td class=\"topbottombar\" colspan=\"2\">";
		$out .= "<input type=\"submit\" value=\"" . "Generate" . "\"/> ";
		$out .= "<input type=\"reset\" value=\"" . "Reset" . "\"/></td></tr>";

		$out .= "</table>";

		$out .= "</form>";
		echo $out;
	}
}

return new GVExport();
?>
