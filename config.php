<?php
/**
 * Default options for GVExport can be set here.
 * Note that the administrator in webtrees can set most of these default values in the control panel settings page for GVExport
 * Control panel settings override these settings
 */
return array(
	// Set path to Graphviz binary
	'graphviz_bin' => '/usr/bin/dot', // Default on Debian Linux
	// 'graphviz_bin' => '/usr/local/bin/dot', // Default if you compiled Graphviz from source
	// 'graphviz_bin' => 'c:\\Graphviz2.17\\bin\\dot.exe', // for Windows (install dot.exe in a directory with no blank spaces)
	// 'graphviz_bin' => '', // Uncomment this line if you don't have GraphViz installed on the server
	'filename' => 'gvexport', // Output file name used for downloads
	'compress_cookie' => true, // Whether cookie data should be compressed - particularly important if users can access multiple trees while logged out
	'output_type' => 'svg', // Default output file type
	'graph_dir' => 'LR', // Direction of graph. 'LR' for Left-to-right, 'TB' for Top-to-bottom
	'mclimit' => '1', // Graphviz MCLIMIT setting - number of times to regenerate graph for reduced crossings
	'diagram_type' => 'decorated', // Default diagram type setting. 'simple', 'decorated', or 'combined'
	'show_photos' => true, // Whether to include photos in diagram
    'photo_shape' => 0, // Default photo shape option
    'photo_size' => "100%", // Default size of photos
	'show_birthdate' => true, // Whether to show birthdate for individuals
	'birthdate_year_only' => false, // Whether to show just the year or the full GEDCOM date of birth
	'show_birthplace' => true, // Whether to show birthplace for individuals
	'show_death_date' => true, // Whether to show death date  for individuals
	'death_date_year_only' => false, // Whether to show just the year or the full GEDCOM date pf death
	'show_death_place' => true, // Whether to show death date for individuals
	'show_marriage_date' => true, // Whether to show marriage date on the family record
	'marr_date_year_only' => false, // Whether to show just the year or the full GEDCOM date of marriage
	'show_marriage_place' => true, // Whether to show the place of marriage on the family record
	'include_ancestors' => true, // If ancestors should be included when calculating who to show in the diagram
	'ancestor_levels' => 2, // Default setting for number of ancestor generations to include
	'include_siblings' => true, // Whether to include siblings when calculating who to include in the diagram
	'include_all_relatives' => true, // Whether to include all relatives (i.e. cousins and nieces/nephews in addition to siblings) when calculating who to include in the diagram
	'include_descendants' => true, // If descendants should be included when calculating who to show in the diagram
	'descendant_levels' => 2, // Default setting for number of descendant generations to include
	'include_spouses' => true, // Whether to include spouses when calculating who to include in the diagram
	'include_all' => false, // Whether to include all linked records regardless of relationship when calculating who to include in the diagram
	'mark_not_related' => false, // Whether to display non-relatives in a different colour
	'faster_relation_check' => false, // Whether to skip checking links outside the displayed tree when checking for non-relatives, to speed up generation of the diagram at the expense of accuracy
	'add_links' => true, // Whether to embed links to the webtrees records in the diagram for supported file types
	'show_xref_individuals' => false, // Whether to show the XREF of individuals
	'show_xref_families' => false, // Whether to show the family XREF
	'use_abbr_place' => 0, // Default abbreviation setting for place names
	'use_abbr_name' => 0, // Default abbreviation settings for individual's names
	'enable_debug_mode' => false, // Debug mode (if set to true then the debug steps are run)
	'show_debug_panel' => false, // If set to true, a debug panel is shown
	'enable_graphviz' => true, // If Graphviz installed, we can still choose not to use it by setting this to false
	'dpi' => '72', // default resolution - increase if text or photos look blurry, decrease if you have memory issues
	'ranksep' => '100%', // Separation between generations as a % of default (e.g. 200% is double spacing)
	'nodesep' => '100%', // Separation between individuals in diagram
	'space_base' => .15, // Base value for above, e.g. 100% is the same as this value
	'xref_list' => '', // Default XREFs to load - normally left blank
	'stop_xref_list' => '', // Default XREFs to stop traversing tree at, normally left blank
	'use_cart' => true, // When true, if there are clippings in the clippings cart then use them
	'show_adv_people' => false, // Whether to show advanced settings by default for People to be included section
	'show_adv_appear' => false, // Whether to show advanced settings by default for Appearance section
	'show_adv_files' => false, // Whether to show advanced settings by default for File settings section
	'typeface' => 0, // Default font value, based on list of font 'typefaces'
	'default_typeface' => 0, // Fallback font value, if above typeface not available
	'font_colour_name' => '#333333',	// Default font colour for name
	'font_colour_details' => '#555555',	// Default font colour for date/place of birth/death etc.
	'font_size' => '10',	// Default font size for everything except name
	'font_size_name' => '12',	// Default font size for name
	'arrows_default' => '#555555', // Default colour for arrows between records
	'arrows_related' => '#222266', // Default colour for arrows from family record to child by birth
	'arrows_not_related' => '#226622',	// Default colour for arrows from family records to child other than birth (adopted, etc)
	'colour_arrow_related' => false, // If arrows should be coloured based on blood-relationship or not
	'male_col' => '#ADD8E6', // Default color of male individuals (light blue)
	'female_col' => '#FFB6C1', // Default color of female individuals (light pink)
	'other_gender_col' => '#FCEAA1', // Default color of Other gender individuals (light yellow)
	'unknown_gender_col' => '#CCEECC', // Default color of unknown gender individuals (light green)
	'male_unrelated_col' => '#EEF8F8', // Default color of not blood-related male individuals
	'female_unrelated_col' => '#FDF2F2', // Default color of not blood-related female individuals
	'oth_gender_unrel_col' => '#FCF7E3', // Default color of not blood-related Other gender individuals
	'unkn_gender_unrel_col' => '#D6EED6', // Default color of not blood-related unknown gender individuals
	'family_col' => '#FFFFEE', // Default color of families (different light yellow)
	'background_col' => '#EEEEEE', // Background of diagram (light grey)
	'indi_background_col' => '#FEFEFE', // Background of individual tile (except simple mode)
	'highlight_start_indis' => false, // Whether to use a different colour for starting individuals
    'no_highlight_xref_list' => '', // XREFs in this list are not highlighted
	'highlight_col' => '#FFFDC3', // Background of starting individuals
	'border_col' => '#606060', // Outline colour
	'birth_prefix' => '*', // Text shown on chart before the birthdate
	'death_prefix' => '†', // Text shown on chart before the death date
    'save_settings_name' => '', // Default value for text field where name of settings can be entered
    'show_diagram_panel' => false, // If set to true, a "Saved diagrams" section is shown at the top, that lists settings saved using the feature to save multiple versions of settings
    'auto_update' => true, // If auto-updating browser render on change is enabled or not
    'url_xref_treatment' => 'default' // What to do with XREF provided by webtrees when loading page ('default', 'add', 'nothing', or 'overwrite'   )
);