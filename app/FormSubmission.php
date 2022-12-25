<?php

namespace vendor\WebtreesModules\gvexport;

class FormSubmission
{
    public function load($vars): array
    {
        $settings = [];
        // INDI id
        if (!empty($vars["other_pids"])) {
            $settings['indi'] = $vars["other_pids"];
        } else {
            $settings['indi'] = "";
        }
        // Stop PIDs
        if (!empty($vars["other_stop_pids"])) {
            $settings['stop_pids'] = $vars["other_stop_pids"];
            $settings['stop_proc'] = true;
        } else {
            $settings['stop_proc'] = false;
        }
        $settings['indiance'] = isset($vars['indiance']);
        $settings['indidesc'] = isset($vars['indidesc']);

        // If "Anyone" option is picked, then other relations options also must be set
        $settings['indisibl'] = isset($vars['indisibl']) || isset($vars['indiany']);
        $settings['indispou'] = isset($vars['indispou']) || isset($vars['indiany']);
        $settings['indirels'] = isset($vars['indirels']) || isset($vars['indiany']);
        $settings['indiany'] = isset($vars['indiany']);

        if (isset($vars['ance_level'])) {
            $settings['ance_level'] = $vars["ance_level"];
        } else {
            $settings['ance_level'] = 0;
        }
        if (isset($vars['desc_level'])) {
            $settings['desc_level'] = $vars["desc_level"];
        } else {
            $settings['desc_level'] = 0;
        }

        if (isset($vars["mclimit"])) {
            $settings['mclimit'] = $vars["mclimit"];
        }

        $settings['marknr'] = isset($vars['marknr']);
        $settings['fastnr'] = isset($vars['fastnr']);

        if (isset($vars['fontcolor_name'])) {
            $settings["fontcolor_name"] = $vars['fontcolor_name'];
        }

        if (isset($vars['fontcolor_details'])) {
            $settings["fontcolor_details"] = $vars['fontcolor_details'];
        }

        if (isset($vars['fontsize'])) {
            $settings['fontsize'] = $vars['fontsize'];
        }

        if (isset($vars['fontsize_name'])) {
            $settings['fontsize_name'] = $vars['fontsize_name'];
        }

        if (isset($vars['typeface'])) {
            $settings['typeface'] = $vars['typeface'];
        }

        if (isset($vars['arrows_default'])) {
            $settings['arrows_default'] = $vars['arrows_default'];
        }

        if (isset($vars['arrows_related'])) {
            $settings['arrows_related'] = $vars['arrows_related'];
        }

        if (isset($vars['arrows_not_related'])) {
            $settings['arrows_not_related'] = $vars['arrows_not_related'];
        }

        if (isset($vars["color_arrow_related"])) {
            $settings['color_arrow_related'] = $vars['color_arrow_related'];
        }

        if (isset($vars['graph_dir'])) {
            $settings['graph_dir'] = $vars['graph_dir'];
        }

        // Which data to show
        $settings['show_by'] = isset($vars['show_by']);
        if (isset($vars['bd_type'])) {
            $settings['bd_type'] = $vars['bd_type'];
        }
        $settings['show_bp'] = isset($vars['show_bp']);
        $settings['show_dy'] = isset($vars['show_dy']);
        if (isset($vars['dd_type'])) {
            $settings['dd_type'] = $vars['dd_type'];
        }
        $settings['show_dp'] = isset($vars['show_dp']);
        $settings['show_my'] = isset($vars['show_my']);
        if (isset($vars['md_type'])) {
            $settings['md_type'] = $vars['md_type'];
        }
        $settings['show_mp'] = isset($vars['show_mp']);
        $settings['show_pid'] = isset($vars['show_pid']);
        $settings['show_fid'] = isset($vars['show_fid']);
        $settings['show_url'] = isset($vars['show_url']);

        if (isset($vars['use_abbr_place'])) {
            $settings['use_abbr_place'] = $vars['use_abbr_place'];
        }

        if (isset($vars['use_abbr_name'])) {
            $settings['use_abbr_name'] = $vars['use_abbr_name'];
        }

        $settings['usecart'] = isset($vars['usecart']);
        $settings['adv_people'] = isset($vars['adv_people']);
        $settings['adv_appear'] = isset($vars['adv_appear']);
        $settings['adv_files'] = isset($vars['adv_files']);
        $settings['auto_update'] = isset($vars['auto_update']);
        $settings['debug'] = isset($vars['debug']);
        $settings['show_debug'] = isset($vars['show_debug']);
        $settings['use_graphviz'] = isset($vars['use_graphviz']);

        // Set custom colors
        if (isset($vars["colorm"])) {
            $settings['colorm'] = $vars["colorm"];
        }
        if (isset($vars["colorf"])) {
            $settings['colorf'] = $vars["colorf"];
        }
        if (isset($vars["colorx"])) {
            $settings['colorx'] = $vars["colorx"];
        }
        if (isset($vars["coloru"])) {
            $settings['coloru'] = $vars["coloru"];
        }
        if (isset($vars["colorm_nr"])) {
            $settings['colorm_nr'] = $vars["colorm_nr"];
        }
        if (isset($vars["colorf_nr"])) {
            $settings['colorf_nr'] = $vars["colorf_nr"];
        }
        if (isset($vars["colorx_nr"])) {
            $settings['colorx_nr'] = $vars["colorx_nr"];
        }
        if (isset($vars["coloru_nr"])) {
            $settings['coloru_nr'] = $vars["coloru_nr"];
        }
        if (isset($vars["colorfam"])) {
            $settings['colorfam'] = $vars["colorfam"];
        }
        if (isset($vars["colorbg"])) {
            $settings['colorbg'] = $vars["colorbg"];
        }
        if (isset($vars["colorindibg"])) {
            $settings['colorindibg'] = $vars["colorindibg"];
        }
        $settings['startcol'] = isset($vars["startcol"]);
        if (isset($vars["colorstartbg"])) {
            $settings['colorstartbg'] = $vars["colorstartbg"];
        }
        if (isset($vars["colorborder"])) {
            $settings['colorborder'] = $vars["colorborder"];
        }
        // Settings
        if (!empty($vars['diagram_type'])) {
            $settings['diagram_type'] = $vars['diagram_type'];

        }
        $settings['with_photos'] = isset($vars['with_photos']);
        if (!empty($vars['no_fams'])) {
            $settings['no_fams'] = $vars['no_fams'];
        }

        if (isset($vars['dpi'])) {
            $settings['dpi'] = $vars['dpi'];
        }
        if (isset($vars['ranksep'])) {
            $settings['ranksep'] = $vars['ranksep'];
        }
        if (isset($vars['nodesep'])) {
            $settings['nodesep'] = $vars['nodesep'];
        }
        return $settings;
    }
}