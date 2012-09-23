<?php
/**
 * Menu for GVExport Module
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2005	John Finlay and Others
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package PhpGedView
 * @subpackage Modules, GVExport
 * @version 0.7.0
 * @author Ferenc Kurucz <korbendallas1976@gmail.com> 
 */
 
//-- security check, only allow access from module.php. Include this code in each of your module files
if ( strstr( $_SERVER["SCRIPT_NAME"], "menu.php")) {
   print "Now, why would you want to do that.  You're not hacking are you?";
   exit;
}

class gvexport_ModuleMenu {
   /**  
     * get the Your Module menu
     * @return Menu 	the menu item
     */
	function &getMenu() {
		global $TEXT_DIRECTION, $PGV_IMAGE_DIR, $PGV_IMAGES, $GEDCOM, $pgv_lang;
		global $PRIV_USER, $PRIV_PUBLIC;
		global $LANGUAGE, $lang_short_cut;		

		if (!file_exists("modules/gvexport.php")) return null;
		if ($PRIV_USER<getUserAccessLevel()) return null;  

		// Load language file (if the localized file does not exists, then it loads the english one)
		if ( !file_exists( "modules/gvexport/languages/lang." . $lang_short_cut[$LANGUAGE] . ".php")) {
			require("modules/gvexport/languages/lang.en.php");
		} else {
			require("modules/gvexport/languages/lang." . $lang_short_cut[$LANGUAGE] . ".php");	
		}
		
		if ($TEXT_DIRECTION=="rtl") $ff="_rtl"; else $ff="";

		//-- main menu item - this uses the icon as the Welcome Page menu
		$menu = new Menu($pgv_lang["gvexport"], "module.php?mod=gvexport", "down");
		if (!empty($PGV_IMAGES["gedcom"]["large"])) {
			$menu->addIcon($PGV_IMAGE_DIR."/".$PGV_IMAGES["gedcom"]["large"]);
		}
		$menu->addClass("menuitem$ff", "menuitem_hover$ff", "submenu$ff");
   
		//First sub menu option
		if (getUserAccessLevel(getUserName())<= $PRIV_USER) {
			if (isset($_REQUEST['pid'])) {
				$submenu= new Menu($pgv_lang["all-in-one_tree"], "module.php?mod=gvexport&action=allinonetree&pid=" . $_REQUEST['pid']);
			} else {
				$submenu= new Menu($pgv_lang["all-in-one_tree"], "module.php?mod=gvexport&action=allinonetree");
			}
			$submenu->addIcon('modules/gvexport/images/gvexport.gif');
			$submenu->addClass("submenuitem$ff", "submenuitem_hover$ff");
			$menu->addSubmenu($submenu);
		}
        //Additional sub menu options can be added by repeating the code above
          
		return $menu;
	}
}
?> 
