<?php

namespace vendor\WebtreesModules\gvexport;
use Fisharebest\Webtrees\I18N;

/**
 * Help object for help information of each option in GVExport
 */
class Help
{
    private array $help;
    public function __construct()
    {
        // Array of help items and the content of each
        $this->help[0][0] = "Include anyone related to";
        $this->help[0][1] = "This is our starting point. Choose a person to base the diagram around. You might choose yourself.</p><p>You can choose as many starting individuals as you wish. A tree will be created for each starting individual based on the settings, then the trees are merged to create one tree for the diagram.</p><p>Click the X to remove a record from the list. If two or more records are listed, a \"Clear all\" option will appear to allow you to remove all records.</p><p>You can also click on an individual in this list to have the diagram in the browser scroll to that person.</p><p>When you next come to the page, the default individual or the last individual you were looking at in webtrees will be loaded. If there was just one saved individual then it will be replaced with the individual provided by webtrees on coming to the page. If there were multiple in the list, then the individual provided by webtrees will be added to the list (if not already in it). So be sure to double-check you are only including individuals you are looking for when first loading the page. This default behaviour can be change with the option \"Treatment of source individuals\" in the advanced section.";
        $this->help[1][0] = "Include ancestors";
        $this->help[1][1] = "Tick this option to include ancestors of your starting person(s) and anyone in the tree.</p><h4>Max levels</h4><p>The number of generations of ancestors to include. For example, if you chose yourself as your starting person, you may want the diagram to include relatives back to your great-grandparents. In this case, you would type \"3\" in this box, to include your parents' generation, your grandparents' generation, and your great-grandparents' generation.";
        $this->help[2][0] = "Include";
        $this->help[2][1] = "This section lets you decide what type of relatives to include when generating the diagram.</p><h4>Siblings</h4><p>Include brothers and sisters of anyone in the tree.</p><h4>All relations</h4><p>Include cousins, nieces, nephews, and their descendants when generating the tree. Requires \"Siblings\" to be selected.</p><h4>Partners</h4><p>Include the husbands, wives, and partners of those in the tree even if they aren't blood-relatives. There is another option \"Mark not blood-related people with different color\" that allows these people to be marked in a different colour.</p><h4>Anyone</h4><p>Follow all links regardless of whether the person is related. In practice, this generally means that the family of non-relatives (i.e. spouses) are included in the tree.</p><p>Use this option to include all records of the selected generations. Note that only records with a link are included, so you may need to include more ancestor generations (or more starting individuals) to get all your records to show.";
        $this->help[3][0] = "Include descendants";
        $this->help[3][1] = "Whether to include children, grandchildren, etc. of people listed in the tree.</p><h4>Max levels</h4><p>The number of generations of descendants to include. For example, if you chose yourself as your starting person, you may want the diagram to include descendants down to your great-grandchildren. In this case, you would type \"3\" in this box, to include your children and others of their generation, your grandchildren, and your great-grandchildren's generation.";
        $this->help[4][0] = "Graph direction";
        $this->help[4][1] = "There are two display options here:<ul><li>Left to right - The default option, columns of generations are created with the older generations to the left and younger to the right.<li>Top to bottom - Rows of generations are created, with the older generations at the top and the younger at the bottom.";
        $this->help[5][0] = "Diagram type";
        $this->help[5][1] = "The options here are:<ul><li>Simple - A box per person, coloured based on sex. No photos allowed.<li>Decorated - A more refined box, which also allows photos to be included.<li>Combined - Each couple is one box instead of one per person. Also allows photos.</ul>";
        $this->help[6][0] = "Output file type";
        $this->help[6][1] = "The output file type you want. This is ignored when rendering in the browser, it is only used when you download a file. File types SVG, PNG, JPG, and PDF can be downloaded, in addition to the Graphviz DOT format.</p><p>There are some minor differences if you have Graphviz installed on the server:<ul><li>There are two more file types, GIF and PostScript (PS). Most people would not need these.<li>PDF files may be smaller and can include URLs<li>You may be able to include more records</ul>";
        $this->help[7][0] = "Browser render";
        $this->help[7][1] = "If the option \"Auto-update\" is selected, the browser rendered diagram will automatically update when any option is changed. This will also hide the \"Update\" button.";
        $this->help[8][0] = "Help";
        $this->help[8][1] = "GVExport is a webtrees module that allows you to create complex visual family trees, using a tool called Graphviz to display the tree. Select a starting person, adjust the settings, and see that person's family tree.</p><p>Some fields have an icon <span class=\"info-icon btn btn-primary\">i</span> next to them, clicking this will give you some more information about that field.</p><p>Advanced configurations are possible by toggling advanced settings by clicking the \"Toggle advanced settings\" option at the end of each section. Clicking the ⛶ in the top right corner of the browser rendering will display the browser rendering in full screen. You can also use the webtrees \"Clippings Cart\" feature to select records, which GVExport can then use instead of the \"People to be included\" settings. </p><p>There are several buttons that take action based on the settings that you have chosen:<ul><li>Update: Clicking this updates the browser render (this is hidden if auto-update is enabled).<li>Download: Clicking this will download the diagram in the format chosen in the \"Output file type\" option. <li>Reset: Clicking this will reset the settings to the default values. Default values can be changed by an administrator in the webtrees Control Panel.<li>Help: Shows this help message.</ul></p><p>For more information, see the <a href=\"https://github.com/Neriderc/GVExport/wiki\" class=\"help-link\">GVExport Wiki</a>.</p><p>You can also ask a question, provide feedback, or suggest features on our <a href=\"https://github.com/Neriderc/GVExport\" class=\"help-link\">GitHub page</a>. Just <a href=\"https://github.com/Neriderc/GVExport/issues/new\" class=\"help-link\">open an issue</a>, or <a href=\"https://github.com/Neriderc/GVExport/issues\" class=\"help-link\">browse existing issues</a>.";
        $this->help[9][0] = "Clippings cart";
        $this->help[9][1] = "Within webtrees, you can add items to the clippings cart. This is usually done by navigating to a record within webtrees then selecting the \"Clippings cart\" option near the top of the screen.</p><p> If you have added individuals and the related family and media records to the clippings cart, these can be used to display the diagram instead of the criteria in the \"People to be included\" section.</p><p>If you have individuals in the clippings cart but don't wish to use these, you have the option to ignore the clippings cart.";
        $this->help[10][0] = "XREFs of included individuals";
        $this->help[10][1] = "This is a list of XREFs that represents the records listed in the \"Include anyone related to\" section. You may edit this box and click the refresh button in order to load your list of XREFs into the selection list.";
        $this->help[11][0] = "Stop processing on";
        $this->help[11][1] = "If the module reaches one of these people, don't grow the tree from here. For example, if you chose yourself as the starting point, you might want to include all of your blood relatives except your mother's side of the family. Add your mother as a person to \"Stop processing on\". The tree will still include your mother, but it won't include her parents, siblings, etc.</p><p>Choose a person in the top box, and they will be added to the list. Their XREF will also be included in the bottom box (which can be edited directly if needed).";
        $this->help[12][0] = "Non-relatives";
        $this->help[12][1] = "This section has two related options.<h4>Mark not blood-related people with different color</h4><p>Where spouses are included and are not blood-related to anyone in the \"Include anyone related to\" field, if this option is selected they will be highlighted in a much duller color than the standard blue/pink for relatives.</p><p>Note that when this option is selected, and non-relatives are found in the selected records, then a full tree scan is performed to identify if an individual is related by a more distant link (see next option).</p><h4>Ignore unseen links to speed up relative check</h4><p>This option is only available when the previous option is selected. When the option to display non-relatives in a different colour is selected, normally a scan is performed starting from the starting individuals and branching to all relatives regardless of display settings. This ensures that if a person is related via a link not displayed, they will still show as related.</p><p>Enabling this option prevents the full scan of the tree which can greatly speed up the generation of the diagram on larger trees, but may reduce accuracy of the colouring of non-relatives.";
        $this->help[13][0] = "Photos";
        $this->help[13][1] = "This section contains the option to display the photo from the individual's page in the tree. This is only supported for Decorated and Combined diagram types.</p><p>The \"Photo shape\" option allows you to select a shape for the photos. Photos will be cropped to fit this shape.</p><p>The \"Photo size\" option allows you to adjust the scale of the photo. 100&percnt; is the default, and you can increase this to make the photos bigger, or decrease it to make them smaller.";
        $this->help[14][0] = "Add URL to individuals and families";
        $this->help[14][1] = "For SVG (and PDF if GraphViz installed on server) output, links can be included. This way you can click on the person in the diagram to be taken to their webtrees page.";
        $this->help[15][0] = "Abbreviated names";
        $this->help[15][1] = "There are several options to shorten the names of individuals displayed.</p><h4>Full name</h4><p>The default option, this shows the primary name and any nickname. This option currently is the only one where the preferred name is underlined, and is the only one to show nicknames (in \"quotes\").<h4>Given and surnames</h4><p>Like the full name, but without nicknames.</p><h4>Given names</h4><p>Just the given names are displayed.<h4>First given names only</h4><p>Just the first word of the name in the given names field.</p><h4>Surnames</h4><p>Just the surnames are displayed.</p><h4>Initials only</h4><p>This displays initials only, for example \"JFK\".</p><h4>Given name initials and Surname</h4><p>This shows initials for the first and second given name, as well as the full surname. For example: \"J.F. Kennedy\"</p><h4>Don't show names</h4><p>No names are shown. If birth and death details are also disabled then it is possible to show only photos or even just coloured boxes.";
        $this->help[16][0] = "Abbreviated place names";
        $this->help[16][1] = "There are several options to shorten place names (for place of birth, place of marriage, place of death).</p><h4>Full place name</h4><p>The place name is printed in full.</p><h4>City and Country</h4><p>The first and last section of the place name is used, using commas to split the sections. For example, \"London, England, United Kingdom\" would be shortened to \"London, United Kingdom\"</p><h4>City and 2 Letter ISO Country Code</h4><p>The first and last section of the place name is used, using commas to split the sections. The country is then converted to the ISO3166-1-Alpha-2 country code. </p><p>For example, \"Calgary, Alberta, Canada\" would be shortened to \"Calgary, CA\"</p><h4>City and 3 Letter ISO Country Code</h4><p>The first and last section of the place name is used, using commas to split the sections. The country is then converted to the ISO3166-1-Alpha-3 country code.</p><p>For example, \"Calgary, Alberta, Canada\" would be shortened to \"Calgary, CAN\"<h4>Note</h4><p>The CLDR display name is used for matching. The data comes from <a href=\"https://www.datahub.io/core/country-codes\" class=\"help-link\">Datahub</a> but has been modified as for example the original CLDR name for the United Kingdom is \"UK\", and the original CLDR name for the United States is \"US\". Matching against these countries for the purposes of abbreviation is therefore pointless. So changes have been made to make this work better. Please <a href=\"https://github.com/Neriderc/GVExport/issues/new\" class=\"help-link\">open an issue</a> on GitHub if you find a country is not abbreviating correctly.";
        $this->help[17][0] = "Show individual XREF";
        $this->help[17][1] = "Whether to include the XREF of individuals in the diagram.";
        $this->help[18][0] = "Show birth date";
        $this->help[18][1] = "Check the box if you would like to include the birthdate of individuals in the output.</p><p>You can also choose whether to show the full date of birth or just the year.";
        $this->help[19][0] = "Show birth place";
        $this->help[19][1] = "Whether to show the place of birth in the output. Also see option for abbreviating place names.";
        $this->help[20][0] = "Show death date";
        $this->help[20][1] = "Check the box if you would like to include the death date of individuals in the output.</p><p>You can also choose whether to show the full date of death or just the year.";
        $this->help[21][0] = "Show death place";
        $this->help[21][1] = "Whether to show the place of death in the output. Also see option for abbreviating place names.";
        $this->help[22][0] = "Show family XREF";
        $this->help[22][1] = "Whether to include the XREF of families in the output.";
        $this->help[23][0] = "Show marriage date";
        $this->help[23][1] = "Check the box if you would like to include the date of marriage in the output.</p><p>You can also choose whether to show the full date of marriage or just the year.";
        $this->help[24][0] = "Show marriage place";
        $this->help[24][1] = "Whether to show the place of marriage in the output. Also see option for abbreviating place names.";
        $this->help[25][0] = "DPI";
        $this->help[25][1] = "For output such as JPG and PNG, this setting will product higher quality output when it is larger, but will also increase the file size. Smaller values will produce smaller files, but the quality will be worse and if the value is too low you may not be able to read text.";
        $this->help[26][0] = "Space";
        $this->help[26][1] = "These options affect the distance between nodes on the diagram.</p><h4>Between generations</h4><p>How close you want generations on the output. For example, for left to right output, smaller numbers will bring the columns of individuals closer together, while larger numbers would push them further apart.</p><h4>Between individuals on the same level</h4><p>How close you want the individuals of the same generation on the output. For example, for left to right output, smaller numbers will mean less space vertically between the boxes, while larger numbers would push them further apart.";
        $this->help[27][0] = "Font";
        $this->help[27][1] = "This section lets you configure the typeface and colour of text.</p><h4>Typeface</h4><p>This list lets you pick from a number of known websafe font typefaces. They must be installed on the system or a fallback typeface will be used. These options are known to be installed on almost all systems and that is why they have been chosen.</p><h4>Font size for names</h4><p>Set the font point size for the names of individuals. In Simple mode this is ignored as all text uses the details font size.</p><h4>Font size for details</h4><p>This changes the font point size for all text except for names. For example, the date and place of marriage, and the date and place of birth and death. In simple mode this is also used for the font size of names.</p><h4>Font colour for names</h4><p>This changes the colour of the font for names. In the simple mode, this sets the font for all text in an individual record's box. Note that the colour picker is provided by your browser, and is handled differently depending on which browser you are using.</p><h4>Font colour for details</h4><p>This changes the colour of the font of all text except for names, for example, the date and place of marriage, and the date and place of birth and death. In the simple mode, this sets the font for the details in the family record (marriage date and place) as the same font is used for the whole individual record (the font colour for names). Note that the colour picker is provided by your browser, and is handled differently depending on which browser you are using.";
        $this->help[28][0] = "Relationship arrows";
        $this->help[28][1] = "This section lets you customise the arrows that link records together.</p><h4>Arrow colour</h4><p>This is the main colour for arrows. This is used for all arrows if the next option is disabled, otherwise it is used for arrows from a spouse to the family record.</p><h4>Show blood relationship in a different colour</h4><p>If this option is selected, the relationship arrows between a family record and children will use different colours depending on if the relationship is by birth, or a different type of relationship (e.g. adoption). If this is disabled, all relationship arrows use the above default colour.</p><ul><li>Related by birth - If the above option is enabled, this sets the colour of arrows from a family record to a child that is related by birth.<li>Related other than by birth - If the above option is enabled, this sets the colour of arrows from a family record to a child that is not related by birth (e.g. foster or adopted).";
        $this->help[29][0] = "Tile colors";
        $this->help[29][1] = "This section allows you to change the colours of the different parts of the tiles shown for each individual. Click the coloured box to change the colour. This uses your browser's colour picker, so will look different depending on what browser you are using.";
        $this->help[30][0] = "Other colours";
        $this->help[30][1] = "This section allows you to change colours associated with the diagram that are not part of the individual tile above. Click the coloured box to change the colour. This uses your browser's colour picker, so will look different depending on what browser you are using.";
        $this->help[31][0] = "Settings file";
        $this->help[31][1] = "If you have saved settings to a file, using the menu on the above saved settings, you can load the settings from the file here.";
        $this->help[32][0] = "Save settings";
        $this->help[32][1] = "Here you are able to save the chosen settings within GVExport. The settings are only accessible within the same user and tree. Click a settings record to load it.</p><p>For example, you may have many diagrams you create from your tree. You may want to save the settings used for each diagram so that in future you can regenerate the diagram to include any new information.</p><p>If you click the ellipsis on a record, you will see several options. <ul><li>Delete - remove these settings</li><li>Download - download a file holding the settings, which can be loaded at a later date.</li><li>Copy link - if logged in, this allows you to share the settings with others by creating a URL to share with them, which is copied to the clipboard of your device.</li><li>Revoke link - if a sharing link has been created, this will show to allow you to revoke the link, so it can no longer be accessed.</li><li>Add to My favourites - If logged in, this will create a sharing link and add this to the webtrees favourites on your \"My page\" page.</li><li>Add to Tree favourites - If logged in as a tree manager, this will create a sharing link and add this to the webtrees favourites on the tree home page.</li></ul>When logged out, only the Delete and Download options will be available.</p><p>The \"Show saved diagrams panel\" option adds a new section to the settings that makes the saved settings available as a dropdown list. You may wish to use webtree's ability for an administrator to \"Masquerade as user\" to set up some settings for another user, then enable this option, so they can easily choose different prepared diagrams without needed to delve into the advanced settings.";
        $this->help[33][0] = "List of diagrams";
        $this->help[33][1] = "You can select from the list to load some saved settings. Settings can be added or changed using the options in the advanced section of the \"File settings\" section.";
        $this->help[34][0] = "Treatment of source individuals";
        $this->help[34][1] = "When you open GVExport, webtrees provides the record of an individual to the chart. This setting dictates what GVExport should do with this individual.</p><p>The options behave as follows:<ul><li>Default - if there is only one record in the starting individuals list, it will be overwritten. Otherwise, the new individual will be added to the list.</li><li>Add to list - The individual is always added to the list of starting individuals.</li><li>Don't add to list - Ignore the individual.</li><li>Overwrite - Clear the list of starting individuals and only add the new individual to the list.</li></ul></p><h4>Individual record</h4><p>The individual provided is decided by webtrees. If you select GVExport while on the page of an individual, this individual is provided to GVExport. When you come from another page, if the tree has a default individual this person is provided, otherwise the first individual in the tree is provided.";
    }

    /**
     * Adds JavaScript code for function that provides the help text
     * @return string
     */
    public function addHelpMessageJavaScript(): string
    {
        $msg = "
// Function to get help text based on identifier
// item - the help item identifier
function getHelpText(item) {
    switch (item) {";

            for ($i=0; $i<sizeof($this->help); $i++) {
                $msg .= "case \"" . $this->help[$i][0] . "\":\n";
                $msg .= "    return '<h3>" . $this->translateClean($this->help[$i][0]) . "</h3>";
                $msg .= "<p>" . $this->translateClean($this->help[$i][1]) . "</p>';\n";
            }
        $msg .= "case \"enable_debug_mode\":
            return '<textarea cols=50 rows=20 onclick=\"this.select()\">' + debug_string + '</textarea>';
        default:
            return  '" . $this->translateClean("Help information not found") . "';
        }
    }";
        return $msg;
    }

    /** Take provided translation and remove any line break, replace ' with &apos; to prevent issue with javascript
     * @param $msg
     * @return string
     */
    private function translateClean($msg): string
    {
        return str_replace(array("\r", "\n"), '',str_replace("'","&apos;",I18N::translate($msg)));
    }
}