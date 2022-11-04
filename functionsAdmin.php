<?php
/* Functions for Control Panel settings page */

/** Retrieve default settings for GVExport
 *  (from Control Panel settings or defaults)
 * @param $module
 * @param bool $reset Whether to return hard coded defaults (true) or admin-set settings (false)
 * @return array
 */
function getAdminSettings($module, bool $reset): array
{
    global $GVE_CONFIG;
    $adminDefaultVars = [
        "otype" => "svg",
        "grdir" => $GVE_CONFIG["default_direction"],
        "mclimit" => $GVE_CONFIG["default_mclimit"],
        "psize" => $GVE_CONFIG["default_pagesize"],
        "indiinc" => "indi",
        "diagtype" => "decorated",
        "with_photos" => "with_photos",
        "show_by" => "show_by",
        "bd_type" => "gedcom",
        "show_bp" => "show_bp",
        "show_dy" => "show_dy",
        "dd_type" => "gedcom",
        "show_dp" => "show_dp",
        "show_my" => "show_my",
        "md_type" => "gedcom",
        "show_mp" => "show_mp",
        "indiance" => "ance",
        "ance_level" => $GVE_CONFIG["settings"]["ance_level"],
        "indisibl" => "sibl",
        "indicous" => "cous",
        "tree_type" => "tree_type",
        "indidesc" => "desc",
        "desc_level" => $GVE_CONFIG["settings"]["desc_level"],
        "indispou" => "spou",
        "indiany" => "",
        "marknr" => "",
        "fastnr" => "",
        "show_url" => "show_url",
        "show_pid" => "DEFAULT", // This is set to DEFAULT, so we can tell if it was loaded from cookie or not
        "show_fid" => "",
        "use_abbr_place" => $GVE_CONFIG["settings"]["use_abbr_place"],
        "use_abbr_name" => $GVE_CONFIG["settings"]["use_abbr_name"],
        "debug" => ($GVE_CONFIG['debug'] ? "debug" : ""),
        "dpi" => $GVE_CONFIG["settings"]["dpi"],
        "ranksep" => $GVE_CONFIG["settings"]["ranksep"],
        "nodesep" => $GVE_CONFIG["settings"]["nodesep"],
        "other_pids" => "",
        "stop_pid" => "",
        "other_stop_pids" => "",
        "download" => TRUE,
        "usecart" => $GVE_CONFIG["settings"]["usecart"],
        "adv_people" => $GVE_CONFIG["settings"]["adv_people"],
        "adv_appear" => $GVE_CONFIG["settings"]["adv_appear"],
        "typeface" => $GVE_CONFIG["default_typeface"],
        "fontcolor_name" => $GVE_CONFIG["dot"]["fontcolor_name"],
        "fontcolor_details" => $GVE_CONFIG["dot"]["fontcolor_details"],
        "fontsize" => $GVE_CONFIG["dot"]["fontsize"],
        "fontsize_name" => $GVE_CONFIG["dot"]["fontsize_name"],
        "arrow_default" => $GVE_CONFIG["dot"]["arrow_default"],
        "arrow_related" => $GVE_CONFIG["dot"]["arrow_related"],
        "arrow_not_related" => $GVE_CONFIG["dot"]["arrow_not_related"],
        "color_arrow_related" => $GVE_CONFIG["settings"]["color_arrow_related"],
        "colorm" => $GVE_CONFIG["dot"]["colorm"],
        "colorf" => $GVE_CONFIG["dot"]["colorf"],
        "colorx" => $GVE_CONFIG["dot"]["colorx"],
        "coloru" => $GVE_CONFIG["dot"]["coloru"],
        "colorm_nr" => $GVE_CONFIG["dot"]["colorm_nr"],
        "colorf_nr" => $GVE_CONFIG["dot"]["colorf_nr"],
        "colorx_nr" => $GVE_CONFIG["dot"]["colorx_nr"],
        "coloru_nr" => $GVE_CONFIG["dot"]["coloru_nr"],
        "colorfam" => $GVE_CONFIG["dot"]["colorfam"],
        "colorbg" => $GVE_CONFIG["dot"]["colorbg"],
        "colorindibg" => $GVE_CONFIG["dot"]["colorindibg"],
        "startcol" => $GVE_CONFIG["settings"]["startcol"],
        "colorstartbg" => $GVE_CONFIG["dot"]["colorstartbg"],
        "colorborder" => $GVE_CONFIG["dot"]["colorborder"],
        "auto_update" => $GVE_CONFIG["settings"]["auto_update"]
    ];
    if (!$reset) {
        foreach ($adminDefaultVars as $preference => $value) {
            $pref = $module->getPreference($preference, "preference not set");
            if ($pref != "preference not set") {
                $adminDefaultVars[$preference] = $pref;
            }
        }
    }
    return $adminDefaultVars;
}

/** Take list of settings and save them as webtrees saved preferences
 * @param $params
 * @param $module
 * @return void
 */
function saveAdminPreferences($params, $module) {
        foreach ($params['vars'] as $preference=>$value) {
            $module->setPreference($preference, $value);
        }
}