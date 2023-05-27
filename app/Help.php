<?php

namespace vendor\WebtreesModules\gvexport;
use Fisharebest\Webtrees\I18N;

/**
 * Help object for help information of each option in GVExport
 */
class Help
{
    private array $help;
    public const NAME_NOT_FOUND = 'Help not found';
    public const NAME_HOME = 'Home';
    public const NAME_PEOPLE_TO_INCLUDE = 'People to be included';
    public const NAME_APPEARANCE = 'Appearance';
    public const NAME_GENERAL_SETTINGS = 'General settings';
    public const NAME_GETTING_STARTED = 'Getting started';
    public const NAME_ABOUT = 'About GVExport';
    public const NAME_INCLUDE_RELATED_TO = 'Include anyone related to';

    private array $help_location = [
            self::NAME_HOME => '',
            self::NAME_NOT_FOUND => '',
            'Getting started' => '',
            self::NAME_ABOUT => '',
            self::NAME_PEOPLE_TO_INCLUDE => '',
            self::NAME_INCLUDE_RELATED_TO => 'People to be included/'
        ];

    public function __construct()
    {
        // Array of help items and the content of each - TODO to be removed when new help system complete
        $this->help[0][0] = "Include anyone related to";
        $this->help[0][1] = "";
        $this->help[1][0] = "Tile contents";
        $this->help[1][1] = "This section allows for some changes to the content of tiles, including links, abbreviations, and the details included on individuals and families.</p><h4>Page links</h4><h5>Add URL to individuals and families</h5><p>For SVG (and PDF if Graphviz installed on server) output, links can be included. This way you can click on the person in the diagram to be taken to their webtrees page.</p><h4>Abbreviations</h4><h5>Abbreviated names</h5><p>There are several options to shorten the names of individuals displayed.</p><ul><li>Full name - the default option, this shows the primary name and any nickname. This option currently is the only one where the preferred name is underlined, and is the only one to show nicknames (in &quot;quotes&quot;).</li><li>Given and surnames - like the full name, but without nicknames.</li><li>Given names - just the given names are displayed.</li><li>First given names only - just the first word of the name in the given names field.</li><li>Surnames - just the surnames are displayed.</li><li>Initials only - this displays initials only, for example &quot;JFK&quot;.</li><li>Given name initials and surname - this shows initials for the first and second given name, as well as the full surname. For example: &quot;J.F. Kennedy&quot;</li><li>Don't show names - no names are shown. If birth and death details are also disabled then it is possible to show only photos or even just coloured boxes.</li></ul><h5>Abbreviated place names</h5><p>There are several options to shorten place names (for place of birth, place of marriage, place of death).</p><ul><li>Full place name - the place name is printed in full.</li><li>City and Country - the first and last section of the place name is used, using commas to split the sections. For example, &quot;London, England, United Kingdom&quot; would be shortened to &quot;London, United Kingdom&quot;</li><li>City and 2-Letter ISO Country Code - the first and last section of the place name is used, using commas to split the sections. The country is then converted to the ISO3166-1-Alpha-2 country code. <br>For example, &quot;Calgary, Alberta, Canada&quot; would be shortened to &quot;Calgary, CA&quot;</li><li>City and 3-Letter ISO Country Code - the first and last section of the place name is used, using commas to split the sections. The country is then converted to the ISO3166-1-Alpha-3 country code.<br>For example, &quot;Calgary, Alberta, Canada&quot; would be shortened to &quot;Calgary, CAN&quot;</li></ul><p>Note - The CLDR display name is used for matching. The data comes from <a href=\"https://www.datahub.io/core/country-codes\" class=\"help-link\">Datahub</a> but has been modified as for example the original CLDR name for the United Kingdom is &quot;UK&quot;, and the original CLDR name for the United States is &quot;US&quot;. Matching against these countries for the purposes of abbreviation is therefore pointless. So changes have been made to make this work better. Please <a href=\"https://github.com/Neriderc/GVExport/issues/new\" class=\"help-link\">open an issue</a> on GitHub if you find a country is not abbreviating correctly.</p><h4>Information on individuals</h4><h5>Show individual XREF</h5><p>Whether to include the XREF of individuals in the diagram.</p><h5>Show birthdate</h5><p>Check the box if you would like to include the birthdate of individuals in the output.</p><p>You can also choose whether to show the full date of birth or just the year.</p><h5>Show birthplace</h5><p>Whether to show the place of birth in the output. Also see option for abbreviating place names.</p><h5>Show death date</h5><p>Check the box if you would like to include the death date of individuals in the output.</p><p>You can also choose whether to show the full date of death or just the year.</p><h5>Show death place</h5><p>Whether to show the place of death in the output. Also see option for abbreviating place names.</p><h5>Show sex of individuals</h5><p>Whether to print the sex of the individual on the individual's tile, e.g. &quot;Male&quot;</p><h4>Information on families</h4><h5>Show family XREF</h5><p>Whether to include the XREF of families in the output.</p><h5>Show marriage date</h5><p>Check the box if you would like to include the date of marriage in the output.</p><p>You can also choose whether to show the full date of marriage or just the year.</p><h5>Show marriage place</h5><p>Whether to show the place of marriage in the output. Also see option for abbreviating place names.";
        $this->help[2][0] = "Connections to include";
        $this->help[2][1] = "This section includes options that let you decide how to build the tree from your starting individual(s).</p><b>Include ancestors</b><p>Tick this option to include ancestors of your starting individual(s) and anyone in the tree.</p><b>Max levels</b><p>The number of generations of ancestors to include. For example, if you chose yourself as your starting person, you may want the diagram to include relatives back to your great-grandparents. In this case, you would type &quot;3&quot; in the Max levels box under Include ancestors, to include your parents' generation, your grandparents' generation, and your great-grandparents' generation.</p><b>Include descendants</b><p>Whether to include children, grandchildren, etc. of people listed in the tree.</p><b>Max levels</b><p>Similar to the same option for &quot;Include ancestors&quot;, this option indicates how many descendant generations should be included.<h4>Relation types to include</h4><p>The type of relatives to include when generating the diagram.</p><b>Siblings</b><p>Include brothers and sisters of anyone in the tree.</p><b>All relations</b><p>Include cousins, nieces, nephews, and their descendants when generating the tree. Requires &quot;Siblings&quot; to be selected.</p><b>Partners</b><p>Include the husbands, wives, and partners of those in the tree even if they aren't blood-relatives. There is another option &quot;Mark not blood-related people with different color&quot; that allows these people to be marked in a different colour.</p><b>Anyone</b><p>Follow all links regardless of whether the person is related. In practice, this generally means that the family of non-relatives (i.e. spouses) are included in the tree.</p><p>Use this option to include all records of the selected generations. Note that only records with a link are included, so you may need to include more ancestor or descendant generations (or more starting individuals) to get all your records to show.";
        $this->help[3][0] = "Diagram";
        $this->help[3][1] = "<h4>Diagram DPI</h4><p>For output such as JPG and PNG, the DPI setting will produce higher quality output when it is larger, but will also increase the file size. Smaller values will produce smaller files, but the quality will be worse and if the value is too low you may not be able to read text.</p><h4>Spacing</h4><p>These options affect the distance between nodes on the diagram.</p><h5>Between generations</h5><p>How close you want generations on the output. For example, for left to right output, smaller numbers will bring the columns of individuals closer together, while larger numbers would push them further apart.</p><h5>Between individuals on the same level</h5><p>How close you want the individuals of the same generation on the output. For example, for left to right output, smaller numbers will mean less space vertically between the boxes, while larger numbers would push them further apart.</p><h4>Diagram colours</h4><p>This section allows you to change colours associated with the diagram that are not directly related to the individual or family tiles. Click the coloured box to change the colour. This uses your browser's colour picker, so will look different depending on what browser you are using. <h5>Diagram background colour<h5><p>This is the colour of the area behind the family tree.</p><h5>Relationship arrow colour</h5><p>The colour of the arrows that show relationships between individuals and families.</p><h5>Show blood relationship in a different colour</h5><p>This shows blood relationships in a different colour than other relationships (e.g. adoption). Selecting this option will provide two colours that can be customised:<ul><li>Related by birth - the arrow colour to use for children related by birth</li><li>Related other than by birth - the arrow colour to use for children not related by birth</li></ul>";
        $this->help[4][0] = "Graph direction";
        $this->help[4][1] = "There are two display options here:<ul><li>Left to right - The default option, columns of generations are created with the older generations to the left and younger to the right.<li>Top to bottom - Rows of generations are created, with the older generations at the top and the younger at the bottom.";
        $this->help[5][0] = "Diagram type";
        $this->help[5][1] = "The options here are:<ul><li>Separated - Individuals each have their own tile, connected by arrows. Family records are separate and also connected by arrows.</li><li>Combined - Each couple is one box instead of one per person. Marriage records are connected under the couple. There are no family records if there are no marriage details to show.</li></ul></p><p>There was previously a third option &quot;Simple&quot;, but this has been removed. The advanced settings in the appearance section now contain all the settings needed to recreate this. Loading a simple diagram should set the appropriate settings to recreate the same look.";
        $this->help[6][0] = "Output file";
        $this->help[6][1] = "This section lets you choose the output file type when downloading the diagram. Some additional settings are available under some circumstances.<h4>Output file type</h4>The output file type you want. This is ignored when rendering in the browser, it is only used when you download a file. File types SVG, PNG, JPG, and PDF can be downloaded, in addition to the Graphviz DOT format.</p><p>There are some minor differences if you have Graphviz installed on the server:<ul><li>There are two more file types, GIF and PostScript (PS). Most people would not need these.</li><li>PDF and SVG files may be smaller and can include URLs</li><li>You may be able to include more records</li></ul><p>In addition, if you have Graphviz installed on the server there are additional quality settings that are available. The following settings are only available when Graphviz is installed on the server, and the output type is set to SVG or PDF:</p><h4>Quality of JPG photos</h4><p>This setting lets you change the quality of embedded JPG photos. This uses the quality setting that is part of the JPG standard. Reducing this can reduce the file size of the output, while increasing it may increase the quality. Also see the DPI setting to change the resolution of the diagram including photos.</p><h4>Convert photos to JPG where possible</h4><p>If enabled, GVExport will attempt to convert PNG, BMP, and GIF files to JPG and will apply the above quality setting.";
        $this->help[7][0] = "Browser render";
        $this->help[7][1] = "If the option &quot;Auto-update&quot; is selected, the browser rendered diagram will automatically update when any option is changed. This will also hide the &quot;Update&quot; button.";
        $this->help[8][0] = "Help";
        $this->help[8][1] = "GVExport is a webtrees module that allows you to create complex visual family trees, using a tool called Graphviz to display the tree. Select a starting person, adjust the settings, and see that person's family tree.</p><p>Some fields have an icon <span class=\"info-icon btn btn-primary\">i</span> next to them, clicking this will give you some more information about that field.</p><p>Advanced configurations are possible by toggling advanced settings by clicking the &quot;Toggle advanced settings&quot; option at the end of each section. Clicking the ⛶ in the top right corner of the browser rendering will display the browser rendering in full screen. Clicking the magnifying glass allows you to search the diagram for an individual.</p><p>You can also use the webtrees &quot;Clippings Cart&quot; feature to select records, which GVExport can then use instead of the &quot;People to be included&quot; settings.</p><p>There are several buttons that take action based on the settings that you have chosen:<ul><li>Update: Clicking this updates the browser render (this is hidden if auto-update is enabled).<li>Download: Clicking this will download the diagram in the format chosen in the &quot;Output file type&quot; option. <li>Reset: Clicking this will reset the settings to the default values. Default values can be changed by an administrator in the webtrees Control Panel.<li>Help: Shows this help message.</ul></p><p>For more information, see the <a href=\"https://github.com/Neriderc/GVExport/wiki\" class=\"help-link\">GVExport Wiki</a>.</p><p>You can also ask a question, provide feedback, or suggest features on our <a href=\"https://github.com/Neriderc/GVExport\" class=\"help-link\">GitHub page</a>. Just <a href=\"https://github.com/Neriderc/GVExport/issues/new\" class=\"help-link\">open an issue</a>, or <a href=\"https://github.com/Neriderc/GVExport/issues\" class=\"help-link\">browse existing issues</a>.";
        $this->help[9][0] = "Clippings cart";
        $this->help[9][1] = "Within webtrees, you can add items to the clippings cart. This is usually done by navigating to a record within webtrees then selecting the &quot;Clippings cart&quot; option near the top of the screen.</p><p> If you have added individuals and the related family and media records to the clippings cart, these can be used to display the diagram instead of the criteria in the &quot;People to be included&quot; section.</p><p>If you have individuals in the clippings cart but don't wish to use these, you have the option to ignore the clippings cart.";
        $this->help[10][0] = "XREFs of included individuals";
        $this->help[10][1] = "This is a list of XREFs that represents the records listed in the &quot;Include anyone related to&quot; section. You may edit this box and click the refresh button in order to load your list of XREFs into the selection list.";
        $this->help[11][0] = "Stop processing on";
        $this->help[11][1] = "If the module reaches one of these people, don't grow the tree from here. For example, if you chose yourself as the starting point, you might want to include all of your blood relatives except your mother's side of the family. Add your mother as a person to &quot;Stop processing on&quot;. The tree will still include your mother, but it won't include her parents, siblings, etc.</p><p>Choose a person in the top box, and they will be added to the list. Their XREF will also be included in the bottom box (which can be edited directly if needed).";
        $this->help[12][0] = "Non-relatives";
        $this->help[12][1] = "This section has two related options.<h4>Mark not blood-related people with different color</h4><p>Where spouses are included and are not blood-related to anyone in the &quot;Include anyone related to&quot; field, if this option is selected they will be highlighted in a much duller color than the standard blue/pink for relatives.</p><p>Note that when this option is selected, and non-relatives are found in the selected records, then a full tree scan is performed to identify if an individual is related by a more distant link (see next option).</p><h4>Ignore unseen links to speed up relative check</h4><p>This option is only available when the previous option is selected. When the option to display non-relatives in a different colour is selected, normally a scan is performed starting from the starting individuals and branching to all relatives regardless of display settings. This ensures that if a person is related via a link not displayed, they will still show as related.</p><p>Enabling this option prevents the full scan of the tree which can greatly speed up the generation of the diagram on larger trees, but may reduce accuracy of the colouring of non-relatives.";
        $this->help[13][0] = "Settings file";
        $this->help[13][1] = "If you have saved settings to a file, using the menu on the above saved settings, you can load the settings from the file here.";
        $this->help[14][0] = "Save settings";
        $this->help[14][1] = "Here you are able to save the chosen settings within GVExport. The settings are only accessible within the same user and tree. Click a settings record to load it.</p><p>For example, you may have many diagrams you create from your tree. You may want to save the settings used for each diagram so that in future you can regenerate the diagram to include any new information.</p><p>If you click the ellipsis on a record, you will see several options. <ul><li>Delete - remove these settings</li><li>Download - download a file holding the settings, which can be loaded at a later date.</li><li>Copy link - if logged in, this allows you to share the settings with others by creating a URL to share with them, which is copied to the clipboard of your device.</li><li>Revoke link - if a sharing link has been created, this will show to allow you to revoke the link, so it can no longer be accessed.</li><li>Add to My favourites - If logged in, this will create a sharing link and add this to the webtrees favourites on your &quot;My page&quot; page.</li><li>Add to Tree favourites - If logged in as a tree manager, this will create a sharing link and add this to the webtrees favourites on the tree home page.</li></ul>When logged out, only the Delete and Download options will be available.</p><p>The &quot;Show saved diagrams panel&quot; option adds a new section to the settings that makes the saved settings available as a dropdown list. You may wish to use webtrees' ability for an administrator to &quot;Masquerade as user&quot; to set up some settings for another user, then enable this option, so they can easily choose different prepared diagrams without needed to delve into the advanced settings.";
        $this->help[15][0] = "List of diagrams";
        $this->help[15][1] = "You can select from the list to load some saved settings. Settings can be added or changed using the options in the advanced section of the &quot;General settings&quot; section.";
        $this->help[16][0] = "Treatment of source individuals";
        $this->help[16][1] = "When you open GVExport, webtrees provides the record of an individual to the chart. This setting dictates what GVExport should do with this individual.</p><p>The options behave as follows:<ul><li>Default - if there is only one record in the starting individuals list, it will be overwritten. Otherwise, the new individual will be added to the list.</li><li>Add to list - The individual is always added to the list of starting individuals.</li><li>Don't add to list - Ignore the individual.</li><li>Overwrite - Clear the list of starting individuals and only add the new individual to the list.</li></ul></p><h4>Individual record</h4><p>The individual provided is decided by webtrees. If you select GVExport while on the page of an individual, this individual is provided to GVExport. When you come from another page, if the tree has a default individual this person is provided, otherwise the first individual in the tree is provided.";
        $this->help[17][0] = "Tile design";
        $this->help[17][1] = "This section allows for some changes to the design of tiles, including the shape, photos, colours, and fonts.</p><h4>Shape</h4><h5>Individual tile shape</h5><p>This option lets you choose the shape for tiles for individuals. There are two options: <ul><li>Rectangle - The default option, the tile is a rectangle shape.</li><li>Rounded rectangle - The tile has rounded corners</li><li>Based on individual's sex - this lets you assign a shape based on the sex of individuals. For example, you can set male individuals to have rectangle tiles and female individuals to have rounded rectangle tiles.</li></ul></p><h4>Photos</h4><h5>Show photos</h5><p>This option lets you enable or disable showing photos on the tiles of individuals. If enabled, there are further options.</p><h5>Photo shape</h5><p>This option lets you choose the shape of photos. Note that if you have Graphviz installed on the server, using these options will disable generating the diagram on the server, which may prevent large diagrams from being created.</p><h5>Photo size</h5><p>This option lets you change the size of photos as they are displayed, which is done as a percentage of the default size. 100&percnt; is the default size, 50&percnt; is half sized and 200&percnt; is double sized.</p><h5>Photo resolution</h5><p>This option lets you change the resolution of photos, which is done by adjusting the width as a percentage of the default width. 100&percnt; is the default resolution, 50&percnt; is half the pixels wide and 200&percnt; is double the pixels wide. Because the aspect ratio is maintained, doubling the resolution will increate the size of the file to around 4 times larger (depending on the exact shape of the photo).</p><h4>Colours</h4><h5>Individual background colour</h5><p>This option lets you set the background colour of an individual's tile. The options are:<ul><li>Custom - the background will be set to the chosen colour</li><li>Based on individual's sex - the background will be coloured based on the sex of the individual.</li></ul></p><h5>Individual stripe colour</h5><p>This option lets you set the colour of the stripe across the top of an individual's tile. The options are:<ul><li>No stripe - no stripe is shown</li><li>Based on individual's sex - the stripe will be coloured based on the sex of the individual.</li></ul><h5>Individual border colour</h5><p>This option lets you set the border colour of an individual's tile. The options are:<ul><li>Custom - the border will be set to the chosen colour</li><li>Based on individual's sex - the border will be coloured based on the sex of the individual.</li><li>Same as family border - the family border colour will be used for the border of individuals.</li></ul></p><h5>Male individuals</h5><p>Set the colour for Male individuals, used in above settings for background, stripe, border if selected.</p><h5>Female individuals</h5><p>Set the colour for Female individuals, used in above settings for background, stripe, border if selected.</p><h5>Other gender individuals</h5><p>Set the colour for Other gender individuals, used in above settings for background, stripe, border if selected.</p><h5>Unknown gender individuals</h5><p>Set the colour for Unknown gender individuals, used in above settings for background, stripe, border if selected.</p><h5>Non-blood related individuals</h5><p>There are four options here for non-blood related individuals, if the &quot;Mark not blood-related people with different color&quot; option is enabled in the &quot;People to be included&quot; section</p><h5>Show starting individuals in different color</h5><p>Whether starting individuals should be highlighted on the diagram. If enabled, you can choose a background colour to highlight the individual with, and can choose to enable this only for some starting individuals.</p><h5>Family background</h5><p>The background colour of family records.</p><h5>Family border colour</h5><p>The border colour of family records.</p><h4>Font</h4><h5>Typeface</h5><p>This list lets you pick from a number of known websafe font typefaces. They must be installed on the system or a fallback typeface will be used. These options are known to be installed on almost all desktop systems and that is why they have been chosen. Mobile browsers will likely use a fallback font.</p><h5>Font size for names</h5><p>Set the font point size for the names of individuals.</p><h5>Font size for details</h5><p>This changes the font point size for all text except for names. For example, the date and place of marriage, and the date and place of birth and death.</p><h5>Font colour for names</h5><p>This changes the colour of the font for names. Note that the colour picker is provided by your browser, and is handled differently depending on which browser you are using.</p><h5>Font colour for details</h5><p>This changes the colour of the font of all text except for names, for example, the date and place of marriage, and the date and place of birth and death. Note that the colour picker is provided by your browser, and is handled differently depending on which browser you are using.";
        $this->help[18][0] = "Message history";
        $this->help[18][1] = "This button shows the history of the pop up notifications. Note that this history is not saved, so it is lost if you leave the page or the page is reloaded.";
        $this->help[19][0] = '';
        $this->help[19][1] = '';
        $this->help[20][0] = '';
        $this->help[20][1] = '';
        $this->help[21][0] = '';
        $this->help[21][1] = '';
        $this->help[22][0] = '';
        $this->help[22][1] = '';
        $this->help[23][0] = '';
        $this->help[23][1] = '';
        $this->help[24][0] = '';
        $this->help[24][1] = '';
        $this->help[25][0] = '';
        $this->help[25][1] = '';
        $this->help[26][0] = '';
        $this->help[26][1] = '';
        $this->help[27][0] = '';
        $this->help[27][1] = '';
        $this->help[28][0] = '';
        $this->help[28][1] = '';
        $this->help[29][0] = '';
        $this->help[29][1] = '';
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
                $msg .= "    return '<h2 class=\"help-title\">" . $this->translateClean($this->help[$i][0]) . "</h2>";
                $msg .= "<p>" . $this->translateClean($this->help[$i][1]) . "</p>';\n";
            }
        $msg .= "case \"enable_debug_mode\":
            return '<textarea cols=50 rows=20 onclick=\"this.select()\">' + debug_string + '</textarea>';
        default:
            return  '" . $this->translateClean("Help information not found") . ": ' + item;
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

    /**
     * Checks if $help is a valid name of a help view
     *
     * @param $help
     * @return bool
     */
    public function helpExists($help): bool
    {
        if (array_key_exists($help, $this->help_location)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the location of the requested help view relative to the Help directory
     *
     * @param $help
     * @return mixed|string
     */
    public function getHelpLocation($help) {
            return $this->help_location[$help];
    }
}