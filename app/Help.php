<?php

namespace vendor\WebtreesModules\gvexport;
use Fisharebest\Webtrees\I18N;

/**
 * Help object for help information of each option in GVExport
 */
class Help
{
    private array $help;
    public const NOT_FOUND = 'Help information not found';
    public const HOME = 'Home';
    public const PEOPLE_TO_INCLUDE = 'People to be included';
    public const APPEARANCE = 'Appearance';
    public const GENERAL_SETTINGS = 'General settings';
    public const GETTING_STARTED = 'Getting started';
    public const ABOUT = 'About GVExport';
    public const DETAILED_INFORMATION = 'Detailed information';
    public const INCLUDE_RELATED_TO = 'Include anyone related to';
    public const CONNECTIONS_TO_INCLUDE = 'Connections to include';
    public const XREFs_OF_INDIVIDUALS = 'XREFs of included individuals';
    public const STOP_PROCESSING_ON = 'Stop processing on';
    public const NON_RELATIVES = 'Non-relatives';
    public const TREATMENT_OF_SOURCE_INDIVIDUALS = 'Treatment of source individuals';
    public const GRAPH_DIRECTION = 'Graph direction';
    public const DIAGRAM_TYPE = 'Diagram type';
    public const TILE_DESIGN = 'Tile design';
    public const TILE_CONTENTS = 'Tile contents';
    public const DIAGRAM_APPEARANCE = 'Diagram';

    private array $help_location = [
            self::HOME => '',
            self::NOT_FOUND => '',
            'Getting started' => '',
            self::ABOUT => '',
            self::DETAILED_INFORMATION => '',
            self::PEOPLE_TO_INCLUDE => 'Detailed information/',
            self::INCLUDE_RELATED_TO => 'Detailed information/People to be included/',
            self::CONNECTIONS_TO_INCLUDE => 'Detailed information/People to be included/',
            self::XREFs_OF_INDIVIDUALS => 'Detailed information/People to be included/',
            self::STOP_PROCESSING_ON => 'Detailed information/People to be included/',
            self::NON_RELATIVES => 'Detailed information/People to be included/',
            self::TREATMENT_OF_SOURCE_INDIVIDUALS => 'Detailed information/People to be included/',
            self::APPEARANCE => 'Detailed information/',
            self::GRAPH_DIRECTION => 'Detailed information/Appearance/',
            self::DIAGRAM_TYPE => 'Detailed information/Appearance/',
            self::TILE_DESIGN => 'Detailed information/Appearance/',
            self::TILE_CONTENTS => 'Detailed information/Appearance/',
            self::DIAGRAM_APPEARANCE => 'Detailed information/Appearance/',
        ];

    public function __construct()
    {
        // Array of help items and the content of each - TODO to be removed when new help system complete
        $this->help[0][0] = "";
        $this->help[0][1] = "";
        $this->help[1][0] = "";
        $this->help[1][1] = "";
        $this->help[2][0] = "";
        $this->help[2][1] = "";
        $this->help[3][0] = "";
        $this->help[3][1] = "";
        $this->help[4][0] = "";
        $this->help[4][1] = "";
        $this->help[5][0] = "";
        $this->help[5][1] = "";
        $this->help[6][0] = "Output file";
        $this->help[6][1] = "This section lets you choose the output file type when downloading the diagram. Some additional settings are available under some circumstances.<h4>Output file type</h4>The output file type you want. This is ignored when rendering in the browser, it is only used when you download a file. File types SVG, PNG, JPG, and PDF can be downloaded, in addition to the Graphviz DOT format.</p><p>There are some minor differences if you have Graphviz installed on the server:<ul><li>There are two more file types, GIF and PostScript (PS). Most people would not need these.</li><li>PDF and SVG files may be smaller and can include URLs</li><li>You may be able to include more records</li></ul><p>In addition, if you have Graphviz installed on the server there are additional quality settings that are available. The following settings are only available when Graphviz is installed on the server, and the output type is set to SVG or PDF:</p><h4>Quality of JPG photos</h4><p>This setting lets you change the quality of embedded JPG photos. This uses the quality setting that is part of the JPG standard. Reducing this can reduce the file size of the output, while increasing it may increase the quality. Also see the DPI setting to change the resolution of the diagram including photos.</p><h4>Convert photos to JPG where possible</h4><p>If enabled, GVExport will attempt to convert PNG, BMP, and GIF files to JPG and will apply the above quality setting.";
        $this->help[7][0] = "Browser render";
        $this->help[7][1] = "If the option &quot;Auto-update&quot; is selected, the browser rendered diagram will automatically update when any option is changed. This will also hide the &quot;Update&quot; button.";
        $this->help[8][0] = "Help";
        $this->help[8][1] = "GVExport is a webtrees module that allows you to create complex visual family trees, using a tool called Graphviz to display the tree. Select a starting person, adjust the settings, and see that person's family tree.</p><p>Some fields have an icon <span class=\"info-icon btn btn-primary\">i</span> next to them, clicking this will give you some more information about that field.</p><p>Advanced configurations are possible by toggling advanced settings by clicking the &quot;Toggle advanced settings&quot; option at the end of each section. Clicking the ⛶ in the top right corner of the browser rendering will display the browser rendering in full screen. Clicking the magnifying glass allows you to search the diagram for an individual.</p><p>You can also use the webtrees &quot;Clippings Cart&quot; feature to select records, which GVExport can then use instead of the &quot;People to be included&quot; settings.</p><p>There are several buttons that take action based on the settings that you have chosen:<ul><li>Update: Clicking this updates the browser render (this is hidden if auto-update is enabled).<li>Download: Clicking this will download the diagram in the format chosen in the &quot;Output file type&quot; option. <li>Reset: Clicking this will reset the settings to the default values. Default values can be changed by an administrator in the webtrees Control Panel.<li>Help: Shows this help message.</ul></p><p>For more information, see the <a href=\"https://github.com/Neriderc/GVExport/wiki\" class=\"help-link\">GVExport Wiki</a>.</p><p>You can also ask a question, provide feedback, or suggest features on our <a href=\"https://github.com/Neriderc/GVExport\" class=\"help-link\">GitHub page</a>. Just <a href=\"https://github.com/Neriderc/GVExport/issues/new\" class=\"help-link\">open an issue</a>, or <a href=\"https://github.com/Neriderc/GVExport/issues\" class=\"help-link\">browse existing issues</a>.";
        $this->help[9][0] = "Clippings cart";
        $this->help[9][1] = "Within webtrees, you can add items to the clippings cart. This is usually done by navigating to a record within webtrees then selecting the &quot;Clippings cart&quot; option near the top of the screen.</p><p> If you have added individuals and the related family and media records to the clippings cart, these can be used to display the diagram instead of the criteria in the &quot;People to be included&quot; section.</p><p>If you have individuals in the clippings cart but don't wish to use these, you have the option to ignore the clippings cart.";
        $this->help[10][0] = "";
        $this->help[10][1] = "";
        $this->help[11][0] = "";
        $this->help[11][1] = "";
        $this->help[12][0] = "Non-relatives";
        $this->help[12][1] = "";
        $this->help[13][0] = "Settings file";
        $this->help[13][1] = "If you have saved settings to a file, using the menu on the above saved settings, you can load the settings from the file here.";
        $this->help[14][0] = "Save settings";
        $this->help[14][1] = "Here you are able to save the chosen settings within GVExport. The settings are only accessible within the same user and tree. Click a settings record to load it.</p><p>For example, you may have many diagrams you create from your tree. You may want to save the settings used for each diagram so that in future you can regenerate the diagram to include any new information.</p><p>If you click the ellipsis on a record, you will see several options. <ul><li>Delete - remove these settings</li><li>Download - download a file holding the settings, which can be loaded at a later date.</li><li>Copy link - if logged in, this allows you to share the settings with others by creating a URL to share with them, which is copied to the clipboard of your device.</li><li>Revoke link - if a sharing link has been created, this will show to allow you to revoke the link, so it can no longer be accessed.</li><li>Add to My favourites - If logged in, this will create a sharing link and add this to the webtrees favourites on your &quot;My page&quot; page.</li><li>Add to Tree favourites - If logged in as a tree manager, this will create a sharing link and add this to the webtrees favourites on the tree home page.</li></ul>When logged out, only the Delete and Download options will be available.</p><p>The &quot;Show saved diagrams panel&quot; option adds a new section to the settings that makes the saved settings available as a dropdown list. You may wish to use webtrees' ability for an administrator to &quot;Masquerade as user&quot; to set up some settings for another user, then enable this option, so they can easily choose different prepared diagrams without needed to delve into the advanced settings.";
        $this->help[15][0] = "List of diagrams";
        $this->help[15][1] = "You can select from the list to load some saved settings. Settings can be added or changed using the options in the advanced section of the &quot;General settings&quot; section.";
        $this->help[16][0] = "Treatment of source individuals";
        $this->help[16][1] = "";
        $this->help[17][0] = "Tile design";
        $this->help[17][1] = "";
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