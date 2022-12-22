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
	'otype' => 'svg', // Default output file type
	'grdir' => 'LR', // Direction of graph
	'mclimit' => '1', // Graphviz MCLIMIT setting - number of times to regenerate graph for reduced crossings
	'diagtype' => 'decorated', // Default diagram type setting
	'with_photos' => true, // Whether to include photos in diagram
	'show_by' => true, // Whether to show birthdate for individuals
	'bd_type' => 'gedcom', // Whether to show just the year or the full GEDCOM date of birth
	'show_bp' => true, // Whether to show birthplace for individuals
	'show_dy' => true, // Whether to show death date  for individuals
	'dd_type' => true, // Whether to show just the year or the full GEDCOM date pf death
	'show_dp' => true, // Whether to show death date for individuals
	'show_my' => true, // Whether to show marriage date on the family record
	'md_type' => 'gedcom', // Whether to show just the year or the full GEDCOM date of marriage
	'show_mp' => true, // Whether to show the place of marriage on the family record
	'indiance' => 'ance', // If ancestors should be included when calculating who to show in the diagram
	'ance_level' => 2, // Default setting for number of ancestor generations to include
	'indisibl' => true, // Whether to include siblings when calculating who to include in the diagram
	'indicous' => true, // Whether to include all relatives (i.e. cousins and nieces/nephews in addition to siblings) when calculating who to include in the diagram
	'indidesc' => true, // If descendants should be included when calculating who to show in the diagram
	'desc_level' => 2, // Default setting for number of descendant generations to include
	'indispou' => true, // Whether to include spouses when calculating who to include in the diagram
	'indiany' => false, // Whether to include all linked records regardless of relationship when calculating who to include in the diagram
	'marknr' => false, // Whether to display non-relatives in a different colour
	'fastnr' => false, // Whether to skip checking links outside the displayed tree when checking for non-relatives, to speed up generation of the diagram
	'show_url' => true, // Whether to embed links to the webtrees records in the diagram for supported file types
	'show_pid' => 'DEFAULT', // This is set to DEFAULT, so we can tell if it was loaded from cookie or not
	'show_fid' => false, // Whether to show the family XREF
	'use_abbr_place' => 'Full place name', // Default abbreviation setting for place names
	'use_abbr_name' => 'Full name', // Default abbreviation settings for individual's names
	'debug' => false, // Debug mode (if set to true then the debug steps are run)
	'show_debug' => false, // If set to true, a debug panel is shown
	'use_graphviz' => true, // If Graphviz installed, we can still choose not to use it
	'dpi' => '72', // default resolution - increase if text or photos look blurry, decrease if you have memory issues
	'ranksep' => '100%', // Separation between generations as a % of default (e.g. 200% is double spacing)
	'nodesep' => '100%', // Separation between individuals in diagram
	'space_base' => .15, // Base value for above, e.g. 100% is the same as this value
	'other_pids' => '', // Default XREFs to load - normally left blank
	'other_stop_pids' => '', // Default XREFs to stop traversing tree at, normally left blank
	'usecart' => true, // When true, if there are clippings in the clippings cart then use them
	'adv_people' => false, // Whether to show advanced settings by default for People to be included section
	'adv_appear' => false, // Whether to show advanced settings by default for Appearance section
	'adv_files' => false, // Whether to show advanced settings by default for File settings section
	'typeface' => 0, // Default font value, based on list of fonts 'typefaces'
	'defaulttypeface' => 0, // Fallback font value, if above typeface not available
	'fontcolor_name' => '#333333',	// Default font colour for name
	'fontcolor_details' => '#555555',	// Default font colour for date/place of birth/death etc.
	'fontsize' => '10',	// Default font size for everything except name
	'fontsize_name' => '12',	// Default font size for name
	'arrow_default' => '#555555', // Default colour for arrows between records
	'arrow_related' => '#222266', // Default colour for arrows from family record to child by birth
	'arrow_not_related' => '#226622',	// Default colour for arrows from family records to child other than birth (adopted, etc)
	'color_arrow_related' => '', // If arrows should be coloured based on blood-relationship or not
	'colorm' => '#ADD8E6', // Default color of male individuals (light blue)
	'colorf' => '#FFB6C1', // Default color of female individuals (light pink)
	'colorx' => '#FCEAA1', // Default color of Other gender individuals (light yellow)
	'coloru' => '#CCEECC', // Default color of unknown gender individuals (light green)
	'colorm_nr' => '#EEF8F8', // Default color of not blood-related male individuals
	'colorf_nr' => '#FDF2F2', // Default color of not blood-related female individuals
	'colorx_nr' => '#FCF7E3', // Default color of not blood-related Other gender individuals
	'coloru_nr' => '#D6EED6', // Default color of not blood-related unknown gender individuals
	'colorfam' => '#FFFFEE', // Default color of families (different light yellow)
	'colorbg' => '#eeeeee', // Background of diagram (light grey)
	'colorindibg' => '#fefefe', // Background of individual tile (except simple mode)
	'startcol' => false, // Whether to use a different colour for starting individuals
	'colorstartbg' => '#FFFDC3', // Background of starting individuals
	'colorborder' => '#606060', // Outline colour
	'birth_text' => '*', // Text shown on chart before the birthdate
	'death_text' => '+', // Text shown on chart before the death date
	'auto_update' => true // If auto-updating browser render on change is enabled or not
);

?>
