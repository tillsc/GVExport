<?php

namespace vendor\WebtreesModules\gvexport;

class FormSubmission
{
    public function load($vars): array
    {
        $settings = [];
        // INDI id
        if (!empty($vars["xref_list"])) {
            $settings['indi'] = $vars["xref_list"];
        } else {
            $settings['indi'] = "";
        }
        // Stop PIDs
        if (!empty($vars["stop_xref_list"])) {
            $settings['stop_pids'] = $vars["stop_xref_list"];
            $settings['stop_proc'] = true;
        } else {
            $settings['stop_proc'] = false;
        }
        $settings['include_ancestors'] = isset($vars['include_ancestors']);
        $settings['include_descendants'] = isset($vars['include_descendants']);

        // If "Anyone" option is picked, then other relations options also must be set
        $settings['include_siblings'] = isset($vars['include_siblings']) || isset($vars['include_all']);
        $settings['include_spouses'] = isset($vars['include_spouses']) || isset($vars['include_all']);
        $settings['include_all_relatives'] = isset($vars['include_all_relatives']) || isset($vars['include_all']);
        $settings['include_all'] = isset($vars['include_all']);

        if (isset($vars['ancestor_levels'])) {
            $settings['ancestor_levels'] = $vars["ancestor_levels"];
        } else {
            $settings['ancestor_levels'] = 0;
        }
        if (isset($vars['descendant_levels'])) {
            $settings['descendant_levels'] = $vars["descendant_levels"];
        } else {
            $settings['descendant_levels'] = 0;
        }

        if (isset($vars["mclimit"])) {
            $settings['mclimit'] = $vars["mclimit"];
        }

        $settings['mark_not_related'] = isset($vars['mark_not_related']);
        $settings['faster_relation_check'] = isset($vars['faster_relation_check']);

        if (isset($vars['font_colour_name'])) {
            $settings["font_colour_name"] = $vars['font_colour_name'];
        }

        if (isset($vars['font_colour_details'])) {
            $settings["font_colour_details"] = $vars['font_colour_details'];
        }

        if (isset($vars['font_size'])) {
            $settings['font_size'] = $vars['font_size'];
        }

        if (isset($vars['font_size_name'])) {
            $settings['font_size_name'] = $vars['font_size_name'];
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

        if (isset($vars["colour_arrow_related"])) {
            $settings['colour_arrow_related'] = $vars['colour_arrow_related'];
        }

        if (isset($vars['graph_dir'])) {
            $settings['graph_dir'] = $vars['graph_dir'];
        }

        // Which data to show
        $settings['show_birthdate'] = isset($vars['show_birthdate']);
        if (isset($vars['birthdate_year_only'])) {
            $settings['birthdate_year_only'] = $vars['birthdate_year_only'] == 'true';
        }
        $settings['show_birthplace'] = isset($vars['show_birthplace']);
        $settings['show_death_date'] = isset($vars['show_death_date']);
        if (isset($vars['death_date_year_only'])) {
            $settings['death_date_year_only'] = $vars['death_date_year_only'] == 'true';
        }
        $settings['show_death_place'] = isset($vars['show_death_place']);
        $settings['show_marriage_date'] = isset($vars['show_marriage_date']);
        if (isset($vars['marr_date_year_only'])) {
            $settings['marr_date_year_only'] = $vars['marr_date_year_only'] == 'true';
        }
        $settings['show_marriage_place'] = isset($vars['show_marriage_place']);
        $settings['show_xref_individuals'] = isset($vars['show_xref_individuals']);
        $settings['show_xref_families'] = isset($vars['show_xref_families']);
        $settings['add_links'] = isset($vars['add_links']);

        if (isset($vars['use_abbr_place'])) {
            $settings['use_abbr_place'] = $vars['use_abbr_place'];
        }

        if (isset($vars['use_abbr_name'])) {
            $settings['use_abbr_name'] = $vars['use_abbr_name'];
        }

        $settings['use_cart'] = isset($vars['use_cart']);
        if (isset($vars['adv_people'])) {
            $settings['adv_people'] = $vars['adv_people'];
        }
        if (isset($vars['adv_appear'])) {
            $settings['adv_appear'] = $vars['adv_appear'];
        }
        if (isset($vars['adv_files'])) {
            $settings['adv_files'] = $vars['adv_files'];
        }
        $settings['auto_update'] = isset($vars['auto_update']);
        $settings['enable_debug_mode'] = isset($vars['enable_debug_mode']);
        $settings['show_debug_panel'] = isset($vars['show_debug_panel']);
        $settings['enable_graphviz'] = isset($vars['enable_graphviz']);

        // Set custom colors
        if (isset($vars["male_col"])) {
            $settings['male_col'] = $vars["male_col"];
        }
        if (isset($vars["female_col"])) {
            $settings['female_col'] = $vars["female_col"];
        }
        if (isset($vars["other_gender_col"])) {
            $settings['other_gender_col'] = $vars["other_gender_col"];
        }
        if (isset($vars["unknown_gender_col"])) {
            $settings['unknown_gender_col'] = $vars["unknown_gender_col"];
        }
        if (isset($vars["male_unrelated_col"])) {
            $settings['male_unrelated_col'] = $vars["male_unrelated_col"];
        }
        if (isset($vars["female_unrelated_col"])) {
            $settings['female_unrelated_col'] = $vars["female_unrelated_col"];
        }
        if (isset($vars["oth_gender_unrel_col"])) {
            $settings['oth_gender_unrel_col'] = $vars["oth_gender_unrel_col"];
        }
        if (isset($vars["unkn_gender_unrel_col"])) {
            $settings['unkn_gender_unrel_col'] = $vars["unkn_gender_unrel_col"];
        }
        if (isset($vars["family_col"])) {
            $settings['family_col'] = $vars["family_col"];
        }
        if (isset($vars["background_col"])) {
            $settings['background_col'] = $vars["background_col"];
        }
        if (isset($vars["indi_background_col"])) {
            $settings['indi_background_col'] = $vars["indi_background_col"];
        }
        $settings['highlight_start_indis'] = isset($vars["highlight_start_indis"]);
        if (isset($vars["highlight_col"])) {
            $settings['highlight_col'] = $vars["highlight_col"];
        }
        if (isset($vars["border_col"])) {
            $settings['border_col'] = $vars["border_col"];
        }
        // Settings
        if (!empty($vars['diagram_type'])) {
            $settings['diagram_type'] = $vars['diagram_type'];

        }
        $settings['show_photos'] = isset($vars['show_photos']);
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