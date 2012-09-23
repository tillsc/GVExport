<?php
/**
 * GVExport Module
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
 * @version 0.8.2
 * @author Ferenc Kurucz <korbendallas1976@gmail.com> 
 */
$pgv_lang["gvexport"] = "GraphViz Export";
$pgv_lang["all-in-one_tree"] = "Teljes családfa";
$pgv_lang["output_file_type"] = "Fájl típusa";
$pgv_lang["diagram_pref"] = "Diagram beállítások";
$pgv_lang["color_m"] = "Férfiak színkódja";
$pgv_lang["color_f"] = "Nők színkódja";
$pgv_lang["color_u"] = "Ismeretlen neműek színkódja";
$pgv_lang["color_fam"] = "Családok színkódja";
$pgv_lang["default"] = "Alapértelmezett";
$pgv_lang["diagram_type"] = "Diagram típus<br/>Egyszerű: Téglalap alakú információs dobozok<br/>Díszített: HTML formázással ellátott információs dobozok";
$pgv_lang["diagram_simple"] = "Egyszerű";
$pgv_lang["diagram_decorated"] = "Díszített";
$pgv_lang["choose_dot"] = "Válassza a DOT kiterjesztést, amennyiben a szerveren<br/>nincs a GraphViz programcsomag installálva.";
// Added in 0.4
$pgv_lang["everyone"] = "Mindenki";
$pgv_lang["indis_include"] = "Diagramon szereplő személyek";
$pgv_lang["related_to"] = "Bárki, aki kapcsolatban áll ezzel a személlyel:";
// Added in 0.4.2
$pgv_lang["include_ance"] = "Ősök hozzáadása";
$pgv_lang["include_desc"] = "Leszármazottak hozzáadása";
$pgv_lang["include_spou"] = "(Házas)társak hozzáadása";
// Added in 0.4.3 and 0.4.4
$pgv_lang["mark_nr"] = "Nem vérrokonok megjelölése más színnel";
$pgv_lang["font_size"] = "Betűméret";
$pgv_lang["include_sibl"] = "Testvérek hozzáadása (addott személynél és annak felmenőinél)";
$pgv_lang["diagram_deco-photo"] = "Díszített + fénykép";
// Added in 0.4.5
$pgv_lang["use_page_break"] = "Tördelés oldalanként";
// Added in 0.4.6
$pgv_lang["mark_not_validated"] = "Ellenőrizetlen adatokkal rendelkező személyek megjelelölése.";
// Added in 0.4.7 (added by Pasquale Ceres)
$pgv_lang["disposition"] = "Fájl letöltése (megjelenítés helyett)";
// Added in 0.4.9
$pgv_lang["graph_direction"] = "Diagram irányítottság";
$pgv_lang["graph_dir_TB"] = "Fentről lefelé";
$pgv_lang["graph_dir_LR"] = "Balról jobbra";
$pgv_lang["personal_data"] = "Ezen személyes adatok szerepeljenek";
$pgv_lang["marriage_data"] = "Ezen házassági adatok szerepeljenek";
$pgv_lang["year"] = "év";
$pgv_lang["place"] = "hely";
$pgv_lang["diagram_combined"] = "Kombinált";
// Added in 0.5.0
$pgv_lang["indi_id"] = "Személy azonosítója";
$pgv_lang["fam_id"] = "Család azonosítója";
$pgv_lang["show_url"] = "Linkek hozzárendelése a nevekhez/családokhoz";
$pgv_lang["show_lt_editor"] = "Show last editor's username";
$pgv_lang["last_change_user"] = "Utoljára módosította";
// Added in 0.5.1
$pgv_lang["num_of_iterations"] = "\"MCLIMIT\" setting, a.k.a. number of iterations which helps to reduce the crossings on the graph.<br />This can be really slow (up to 10..15x compared to default (20) setting)";
// added in 0.5.2 by wooc
$pgv_lang["generate"] = "Elkészít";
$pgv_lang["reset"] = "Visszaállít";
// Added in 0.6.0
$pgv_lang["output_settings"] = "Kimenet";
$pgv_lang["appearance"] = "Megjelenítés";
$pgv_lang["advanced_settings"] = "Egyéb";
$pgv_lang["debug_mode"] = "Debug üzemmód";
$pgv_lang["debug_descr"] = "A DOT fájl és egyéb debug adatok a képernyőre íródnak";
$pgv_lang["abbr_places"] = "Rövidített helységnevek";
$pgv_lang["media_dir"] = "Egyedi media könyvtár";
$pgv_lang["media_dir_descr"] = "A \"/media/thumbs\" alkönyvtárat a PGV automatikusan hozzáadja.";
// Added in 0.6.2
$pgv_lang["max_levels"] = "Max szint";
$pgv_lang["include_cous"] = "Unokatestvérek hozzáadása";
// Added in 0.6.4
$pgv_lang["font_name"] = "Betűméret";
// Added in 0.6.6
$pgv_lang["fulldate"] = "Teljes GEDCOM dátum";
// Added in 0.7.2
$pgv_lang["tree_type"] = "Fa típusa";
$pgv_lang["no_fams"] = "Családok nélkül, csak személyek";
// Added after 0.7.2 ESL!!! 20090213
$pgv_lang["diagtypeCombinedWithPhoto"] = "Fényképek megjelenítése (díszített/kombinált esetében)";
// Added in 0.8.2
$pgv_lang['stop_pids'] = "Családfa feldolgozásának megállítása ezen személyek esetében:";
// Added in 0.8.3
$pgv_lang['graph_look'] = "Családfa végleges kinézete. DPI: a képfájlok esetében<br />RANKSEP: generációk közötti távolság, NODESEP: személyek közötti távolság (azonos szinten)";
?>