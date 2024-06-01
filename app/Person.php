<?php

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Age;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;

/**
 * Represents an individual in the diagram or DOT file
 */
class Person
{
    const SHAPE_NONE = '0';
    const SHAPE_OVAL = '10';
    const SHAPE_CIRCLE = '20';
    const SHAPE_SQUARE = '30';
    const SHAPE_ROUNDED_RECT = '40';
    const SHAPE_ROUNDED_SQUARE = '50';
    const TILE_SHAPE_ROUNDED = 10;
    const TILE_SHAPE_SEX = 20;
    const TILE_SHAPE_VITAL = 30;
    public array $attributes;
    private Dot $dot;

    function __construct($attributes, $dot)
    {
        $this->attributes = $attributes;
        $this->dot = $dot;
    }

    /**
     * Prints the line in the DOT for a person.
     *
     * @param SharedNoteList $sharednotes
     * @return string
     */
    function printPerson(SharedNoteList $sharednotes): string
    {
        $out = "";
        $out .= Dot::convertID($this->attributes['pid']); // Convert the ID, so linked GEDCOMs are displayed properly
        $out .= " [ label=<";
        $out .= $this->printPersonLabel($this->attributes['pid'], $sharednotes, $this->attributes['rel']);
        $out .= ">";
        $out .= "];\n";

        return $out;
    }


    /**
     * Add the DOT code to include this individual in the diagram.
     *
     * @param string $pid
     * @param string $out
     * @param SharedNoteList $sharednotes
     * @return string
     */
    public function addPersonLabel(string $pid, string $out, SharedNoteList $sharednotes): string
    {
        $i = $this->dot->getUpdatedPerson($pid);
        if (isset($this->dot->individuals[$pid]['rel']) && !$this->dot->individuals[$pid]['rel']) {
            $related = FALSE;
        } else {
            $related = TRUE;
        }
        switch ($this->dot->settings['border_col_type']) {
            case Settings::OPTION_BORDER_SEX_COLOUR:
                $border_colour = $this->dot->getGenderColour($i->sex(), $related);
                break;
            case Settings::OPTION_BORDER_CUSTOM_COLOUR:
                $border_colour = $this->dot->settings["indi_border_col"];
                break;
            case Settings::OPTION_BORDER_VITAL_COLOUR:
                $border_colour = $this->getVitalColour($i->isDead(), Settings::OPTION_BORDER_VITAL_COLOUR);
                break;
            case Settings::OPTION_BORDER_AGE_COLOUR:
                $border_colour = $this->getAgeColour($i, Settings::OPTION_BORDER_AGE_COLOUR);
                break;
            default:
                $border_colour = $this->dot->settings["border_col"];
        }
        $out .= "<TD COLOR=\"" . $border_colour . "\"  BORDER=\"1\" CELLPADDING=\"0\" PORT=\"" . $pid . "\">";
        $out .= $this->printPersonLabel($pid, $sharednotes, $related);
        $out .= "</TD>";
        return $out;
    }


    /**
     * Prints the data for a single person.
     *
     * @param string $pid Person ID
     * @param SharedNoteList $sharednotes
     * @param bool $related
     * @return string
     */
    function printPersonLabel(string $pid, SharedNoteList $sharednotes, bool $related = TRUE): string
    {
        $sex = '';
        $out = '';
        $border_colour = $this->dot->settings["border_col"];    // Border colour of the INDI's box
        $death_place = "";
        $i = $this->dot->getUpdatedPerson($pid);
        // Get the personal data
        if ($this->dot->settings["diagram_type"] == "combined" && (substr($pid, 0, 3) == "I_H" || substr($pid, 0, 3) == "I_W")) {
            // In case of dummy individual
            $sex_colour = $this->dot->getGenderColour('U', false);
            $is_dead = false;
            $death_date = "";
            $birthdate = "";
            $birthplace = "";
            $link = "";
            $name = " ";
        } else {
            $sex_colour = $this->dot->getGenderColour($i->sex(), $related);
            switch ($this->dot->settings['border_col_type']) {
                case Settings::OPTION_BORDER_SEX_COLOUR:
                    $border_colour = $this->dot->getGenderColour($i->sex(), $related);
                    break;
                case Settings::OPTION_BORDER_CUSTOM_COLOUR:
                    $border_colour = $this->dot->settings["indi_border_col"];
                    break;
                case Settings::OPTION_BORDER_VITAL_COLOUR:
                    $border_colour = $this->getVitalColour($i->isDead(), Settings::OPTION_BORDER_VITAL_COLOUR);
                    break;
                case Settings::OPTION_BORDER_AGE_COLOUR:
                    $border_colour = $this->getAgeColour($i, Settings::OPTION_BORDER_AGE_COLOUR);
                    break;
            }
            $is_dead = $i->isDead();
            $link = $i->url();

            // --- Birth date ---
            if ($this->dot->settings["show_birthdate"]) {
                $birthdate = Dot::formatDate($i->getBirthDate(), $this->dot->settings["birthdate_year_only"],  $this->dot->settings["use_abbr_month"]);
            } else {
                $birthdate = "";
            }

            if ($this->dot->settings["show_birthplace"]) {
                // Show birthplace
                $birthplace = Dot::getAbbreviatedPlace($i->getBirthPlace()->gedcomName(), $this->dot->settings);
            } else {
                $birthplace = "";
            }

            // --- Death date ---
            if ($this->dot->settings["show_death_date"]) {
                $death_date = Dot::formatDate($i->getDeathDate(), $this->dot->settings["death_date_year_only"],  $this->dot->settings["use_abbr_month"]);
            } else {
                $death_date = "";
            }
            if ($this->dot->settings["show_death_place"]) {
                // Show death place
                $death_place = Dot::getAbbreviatedPlace($i->getDeathPlace()->gedcomName(), $this->dot->settings);
            }
            // --- Name ---
            $names = $i->getAllNames();
            $nameArray = $names[$i->getPrimaryName()];
            $name = $this->getFormattedName($nameArray, $pid);

            if ($i->getPrimaryName() !== $i->getSecondaryName()) {
                $altNameArray = $names[$i->getSecondaryName()];
                $add_name = $this->getFormattedName($altNameArray, "");
                if (!empty($add_name) && trim($add_name) !== "" && $this->dot->settings["use_abbr_name"] < 50) {
                    $name .= '<BR />' . $add_name;//@@ Meliza Amity
                }
            }
            if ($this->dot->settings['show_indi_sex']) {
                $sex = $this->getSexFull($i);
            }
        }


        // --- Printing the INDI details ---
        // Convert birth & death place to get rid of characters which mess up the HTML output
        $birthplace = Dot::convertToHTMLSC($birthplace);
        if ($is_dead) {
            $death_place = Dot::convertToHTMLSC($death_place);
        }
        if ($this->dot->settings["add_links"] || !isset($_REQUEST["download"])) {
            $href = "TARGET=\"_blank\" HREF=\"" . Dot::convertToHTMLSC($link) . "\"";
        } else {
            $href = "";
        }

        // Get background colour
        if ($this->isStartingIndividual($pid) && $this->dot->settings['highlight_start_indis'] == "true" && !$this->isValueInList($this->dot->settings['no_highlight_xref_list'], $pid)) {
            $indi_bg_colour = $this->dot->settings["highlight_col"];
        } else if ($this->dot->settings["sharednote_col_enable"] && $sharednotes->indiHasSharedNote($pid)) {
            $indi_bg_colour = $sharednotes->getSharedNoteColour($pid);
        } else {
            switch ($this->dot->settings['bg_col_type']) {
                case Settings::OPTION_BACKGROUND_CUSTOM_COLOUR:
                default:
                    $indi_bg_colour = $this->dot->settings["indi_background_col"];
                    break;
                case Settings::OPTION_BACKGROUND_SEX_COLOUR:
                    $indi_bg_colour = $this->dot->getGenderColour($i->sex(), $related);
                    break;
                case Settings::OPTION_BACKGROUND_VITAL_COLOUR:
                    $indi_bg_colour = $this->getVitalColour($i->isDead(), Settings::OPTION_BACKGROUND_VITAL_COLOUR);
                    break;
                case Settings::OPTION_BACKGROUND_AGE_COLOUR:
                    $indi_bg_colour = $this->getAgeColour($i, Settings::OPTION_BACKGROUND_AGE_COLOUR);
                    break;
            }
        }

        // Draw table
        if ($this->dot->settings["diagram_type"] == "combined") {
            $out .= "<TABLE BORDER=\"0\" CELLBORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" BGCOLOR=\"" . $indi_bg_colour . "\" $href>";
        } else {
            $style = ($this->shouldBeRounded($i, $this->dot->settings['indi_tile_shape']) ? 'STYLE="ROUNDED" ' : '');
            $out .= "<TABLE " . $style . "COLOR=\"" . $border_colour . "\" BORDER=\"1\" CELLBORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\" BGCOLOR=\"" . $indi_bg_colour . "\" $href>";
        }
        $birthData = " $birthdate " . (empty($birthplace) ? "" : "($birthplace)");
        $deathData = " $death_date " . (empty($death_place) ? "" : "($death_place)");

        $detailsExist = trim($name . $birthData . $deathData . $sex) != "";

        if (!$detailsExist && !$this->dot->settings["show_photos"]) {
            // No information in our tiles so make coloured boxes
            $size = "WIDTH=\"" . ($this->dot->settings["font_size"] * 3) . "\" HEIGHT=\"" . ($this->dot->settings["font_size"] * 3) . "\"";
        } else {
            $size = ""; // Let it sort out size itself
        }

        switch ($this->dot->settings['stripe_col_type']) {
            case Settings::OPTION_STRIPE_SEX_COLOUR:
                $stripe_colour = $sex_colour;
                break;
            case Settings::OPTION_STRIPE_VITAL_COLOUR:
                $stripe_colour = $this->getVitalColour($i->isDead(),Settings::OPTION_STRIPE_VITAL_COLOUR);
                break;
            case Settings::OPTION_STRIPE_AGE_COLOUR:
                $stripe_colour = $this->getAgeColour($i,Settings::OPTION_STRIPE_AGE_COLOUR);
                break;
            default:
                $stripe_colour = '';
        }
        if ($stripe_colour !== '') {
            $out .= "<TR><TD COLSPAN=\"3\" CELLPADDING=\"2\" BGCOLOR=\"$stripe_colour\" PORT=\"nam\" $size></TD></TR>";
        }
        // Second row (photo, name, birth & death data)
        if ($detailsExist || $this->dot->settings["show_photos"]) {
            $out .= "<TR>";
            // Show photo
            if ($this->dot->settings["show_photos"]) {
                if (isset($this->dot->individuals[$pid]["pic"]) && !empty($this->dot->individuals[$pid]["pic"])) {
                    $photo_size = floatval($this->dot->settings["photo_size"]) / 100;
                    $padding = $this->getPhotoPaddingSize();
                    $out .= "<TD ROWSPAN=\"2\" CELLPADDING=\"$padding\" PORT=\"pic\" WIDTH=\"" . ($this->dot->settings["font_size"] * 4 * $photo_size)  . "\" HEIGHT=\"" . ($this->dot->settings["font_size"] * 4 * $photo_size) . "\" FIXEDSIZE=\"true\" ALIGN=\"CENTER\"><IMG SCALE=\"true\" SRC=\"" . $this->dot->individuals[$pid]["pic"] . "\" /></TD>";
                } else {
                    // Blank cell zero width to keep the height right
                    $out .= "<TD ROWSPAN=\"2\" CELLPADDING=\"1\" PORT=\"pic\" WIDTH=\"" . ($detailsExist ? "0" : ($this->dot->settings["font_size"] * 3.5)) . "\" HEIGHT=\"" . ($this->dot->settings["font_size"] * 4) . "\" FIXEDSIZE=\"true\"></TD>";
                }
            }
            if ($detailsExist) {
                $out .= "<TD ALIGN=\"LEFT\" BALIGN=\"LEFT\"  TARGET=\"_BLANK\" CELLPADDING=\"4\" PORT=\"dat\">";
            }
            // Show name
            if (trim($name) != "") {
                $out .= "<FONT COLOR=\"" . $this->dot->settings["font_colour_name"] . "\" POINT-SIZE=\"" . ($this->dot->settings["font_size_name"]) . "\">" . $name . "</FONT>";
                if (trim($birthData . $deathData . $sex) != "") {
                    $out .= "<BR />";
                }
            }
            // Show sex
            if (trim($sex) != "") {
                $out .= "<FONT COLOR=\"" . $this->dot->settings["font_colour_details"] . "\" POINT-SIZE=\"" . ($this->dot->settings["font_size"]) . "\">" . $sex . "</FONT>";
                if (trim($birthData . $deathData) != "") {
                    $out .= "<BR />";
                }
            }
            if (trim($birthData) != "") {
                $out .= "<FONT COLOR=\"" . $this->dot->settings["font_colour_details"] . "\" POINT-SIZE=\"" . ($this->dot->settings["font_size"]) . "\">" . $this->dot->settings["birth_prefix"] . $birthData . "</FONT>";
                if (trim($deathData) != "") {
                    $out .= "<BR />";
                }
            }
            if ($is_dead && trim($deathData) !== "") {
                $out .= "<FONT COLOR=\"" . $this->dot->settings["font_colour_details"] . "\" POINT-SIZE=\"" . ($this->dot->settings["font_size"]) . "\">" . $this->dot->settings["death_prefix"] . $deathData . "</FONT>";
            } else {
                $out .= " ";
            }

            if ($detailsExist) {
                $out .= "</TD>";
            }
            $out .= "<TD CELLPADDING=\"10\"></TD></TR>";
        }
        // Close table
        $out .= "</TABLE>";

        return $out;
    }

    /**
     * Add formatting to name before adding to DOT
     *
     * @param array $nameArray webtrees name array for the person
     * @param string $pid XREF of the person, for adding to name if enabled
     * @return string Returns formatted name
     */
    public function getFormattedName(array $nameArray, string $pid): string
    {
        if (isset($nameArray['full'])) {
            $name = $this->getAbbreviatedName($nameArray);
        } else {
            $name = $nameArray[0];
        }

        // Tidy webtrees terms for missing names
        $name = str_replace(array("@N.N.", "@P.N."), "...", $name);
        // Show nickname in quotes
        $name = str_replace(array('<q class="wt-nickname">', '</q>'), array('"', '"'), $name);
        // Show preferred name as underlined by replacing span with underline tags
        $pos_start = strpos($name, '<span class="starredname">');
        while ($pos_start != false) {
            // Start by replacing the </span>
            $pos_end = strpos(substr($name, $pos_start), "</span>") + $pos_start;
            if ($pos_end) {
                $name = substr_replace($name, "_/U_", $pos_end, strlen("</span>"));
            }

            // Next do the starting tags
            $pos_start = strpos($name, '<span class="starredname">');
            if ($pos_start !== false) {
                $name = substr_replace($name, "_U_", $pos_start, strlen('<span class="starredname">'));
            }
            $pos_start = strpos($name, '<span class="starredname">');
        }
        $name = strip_tags($name);

        // We use _ instead of < >, remove tags, then switch them to proper tags. This lets
        // us control the tags included in an environment where we don't normally have control
        $name = str_replace("_U__/U_", "", $name); // remove blank tags
        $name = str_replace("_U_", "<u>", $name);
        $name = str_replace("_/U_", "</u> ", $name);

        // If PID already in name (from another module), remove it, so we don't add twice
        $name = str_replace(" (" . $pid . ")", "", $name);
        if ($this->dot->settings["show_xref_individuals"] && !isset($vars["first_run_xref_check"])) {
            // Show INDI id
            $name = $name . " (" . $pid . ")";
        }
        return $name;
    }


    /**
     * Abbreviate name based on settings
     *
     * @param array $nameArray array of names from webtrees
     * @return string
     */
    private function getAbbreviatedName(array $nameArray): string
    {
        switch ($this->dot->settings["use_abbr_name"]) {
            case 0: /* Full name */
                return $nameArray["full"];
            case 10: /* Given and Surnames */
                return $nameArray["givn"] . " " . $nameArray["surn"];
            case 20: /* Given names */
                return $nameArray["givn"];
            case 30: /* First given name only */
                return explode(" ", $nameArray["givn"])[0];
            case 40: /* Surname(s) */
                return $nameArray["surn"];
            case 80: /* Preferred given name and surname */
                $pattern = '/<span class="starredname">(.*?)<\/span>/';
                preg_match_all($pattern, $nameArray['full'], $matches);
                $preferredName = $matches[1];
                if (empty($preferredName)) {
                    $preferredName = explode(" ", $nameArray["givn"])[0];
                } else {
                    $preferredName = implode(' ', $preferredName);
                }
                return $preferredName . ' ' . $nameArray['surname'];
            case 50: /* Initials only */
                // Split by space or hyphen to get different names
                $givenParts = preg_split('/[\s-]/', $nameArray["givn"]);
                $initials = substr($givenParts[0], 0, 1);
                if (isset($givenParts[1])) {
                    $initials .= substr($givenParts[1], 0, 1);
                }
                $surnameParts = preg_split('/[\s-]/', $nameArray["surn"]);
                if (substr($surnameParts[0], 0, 1) != "@") {
                    $initials .= substr($surnameParts[0], 0, 1);
                    if (isset($surnameParts[1])) {
                        // If there is a hyphen in the surname found before the first space
                        $spacePos = strpos($nameArray["surn"], " ");
                        if (strpos(substr($nameArray["surn"], 0, $spacePos ?: strlen($nameArray["surn"])), "-")) {
                            $initials .= "-";
                        }
                        $initials .= substr($surnameParts[1], 0, 1);
                    }
                }
                return $initials;
            case 60: /* Given name initials and Surname */
                // Split by space or hyphen to get different names
                $givenParts = preg_split('/[\s-]/', $nameArray["givn"]);
                $initials = substr($givenParts[0], 0, 1) . ".";
                if (isset($givenParts[1])) {
                    $initials .= substr($givenParts[1], 0, 1) . ".";
                }
                return $initials . " " . $nameArray["surn"];
            case 70: /* Don't show names */
                return " ";
            default:
                return $nameArray["full"];

        }
    }

    /**
     *  Check if XREF in list of starting individuals
     *
     * @param string $pid Xref to check
     * @return bool
     */
    private function isStartingIndividual(string $pid): bool
    {
        $indis = explode(",", $this->dot->settings['xref_list']);
        for ($i = 0; $i < count($indis); $i++) {
            if (trim($indis[$i]) == $pid) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if string is found in full in comma separated list
     *
     * @param $list
     * @param string $value
     * @return bool
     */
    private function isValueInList($list, string $value): bool
    {
        $list = explode(',', $list);
        return in_array($value, $list);
    }

    /**
     * Given an individual, return the sex of the individual in full
     *
     * @param Individual|null $i
     * @return string
     */
    private function getSexFull(?Individual $i): string
    {
        switch ($i->sex()) {
            case 'F':
                return I18N::translate('Female');
            case 'M':
                return I18N::translate('Male');
            case 'X':
                return I18N::translate('Other');
            case 'U':
                return I18N::translate('Unknown');
            default:
                return "";
        }
    }

    /**
     * Returns the cell margin needed for the different photo shapes, so
     * they don't overlap rounded rectangle borders
     *
     * @return int
     */
    private function getPhotoPaddingSize(): int
    {
        if ($this->dot->settings['indi_tile_shape'] == self::TILE_SHAPE_ROUNDED) {
            switch ($this->dot->settings['photo_shape']) {
                case self::SHAPE_NONE:
                    return 4;
                case self::SHAPE_SQUARE:
                    return 2;
                default:
            }
        }
        return 1;
    }

    /**
     * Calculate whether this individual's tile should have
     * rounded corners based on the settings
     *
     * @param Individual $i
     * @param int $option
     * @return bool
     */
    private function shouldBeRounded(Individual $i, int $option): bool
    {
        switch ($option) {
            case 0:
            default;
                return false;
            case 10:
                return true;
            case Person::TILE_SHAPE_SEX:
                switch ($i->sex()) {
                    case 'M':
                        return $this->shouldBeRounded($i, $this->dot->settings['shape_sex_male']);
                    case 'F':
                        return $this->shouldBeRounded($i, $this->dot->settings['shape_sex_female']);
                    case 'X':
                        return $this->shouldBeRounded($i, $this->dot->settings['shape_sex_other']);
                    case 'U':
                        return $this->shouldBeRounded($i, $this->dot->settings['shape_sex_unknown']);
                    default: return false;
                }
            case Person::TILE_SHAPE_VITAL:
                if ($i->isDead()) {
                    return $this->dot->settings['shape_vital_dead'];
                } else {
                    return $this->dot->settings['shape_vital_living'];
                }
        }
    }

    /**
     * Retrieve colour to represent the status of living or deceased, based on $context
     *
     * @param string $is_dead
     * @param $context
     * @return mixed|string
     */
    private function getVitalColour(string $is_dead, $context)
    {
        if ($is_dead) {
            switch($context) {
                case Settings::OPTION_BACKGROUND_VITAL_COLOUR:
                    return $this->dot->settings['indi_background_dead_col'];
                case Settings::OPTION_STRIPE_VITAL_COLOUR:
                    return $this->dot->settings['indi_stripe_dead_col'];
                case Settings::OPTION_BORDER_VITAL_COLOUR:
                    return $this->dot->settings['indi_border_dead_col'];
            }
        } else {
            switch($context) {
                case Settings::OPTION_BACKGROUND_VITAL_COLOUR:
                    return $this->dot->settings['indi_background_living_col'];
                case Settings::OPTION_STRIPE_VITAL_COLOUR:
                    return $this->dot->settings['indi_stripe_living_col'];
                case Settings::OPTION_BORDER_VITAL_COLOUR:
                    return $this->dot->settings['indi_border_living_col'];
            }
        }
        return '#000000';
    }

    /**
     * Calculate colour to represent the age of the individual
     *
     * @param $individual
     * @param $context
     * @return string
     */
    private function getAgeColour($individual, $context): string
    {
        if ($individual->isDead()) {
            $age = (string) new Age($individual->getBirthDate(), $individual->getDeathDate());
        } else {
            $today = new Date(strtoupper(date('d M Y')));
            $age   = (string) new Age($individual->getBirthDate(), $today);
        }
        if ($age === '') {
            switch ($context) {
                case Settings::OPTION_BACKGROUND_AGE_COLOUR:
                    return $this->dot->settings['indi_background_age_unknown_col'];
                case Settings::OPTION_BORDER_AGE_COLOUR:
                    return $this->dot->settings['indi_border_age_unknown_col'];
                case Settings::OPTION_STRIPE_AGE_COLOUR:
                    return $this->dot->settings['indi_stripe_age_unknown_col'];
            }

        } else {
            $age = (int) $age;
        }
        switch ($context) {
            case Settings::OPTION_BACKGROUND_AGE_COLOUR:
            default:
                $low_col = $this->dot->settings['indi_background_age_low_col'];
                $high_col = $this->dot->settings['indi_background_age_high_col'];
                $low_age = $this->dot->settings['indi_background_age_low'];
                $high_age = $this->dot->settings['indi_background_age_high'];
                break;
            case Settings::OPTION_BORDER_AGE_COLOUR:
                $low_col = $this->dot->settings['indi_border_age_low_col'];
                $high_col = $this->dot->settings['indi_border_age_high_col'];
                $low_age = $this->dot->settings['indi_border_age_low'];
                $high_age = $this->dot->settings['indi_border_age_high'];
                break;
            case Settings::OPTION_STRIPE_AGE_COLOUR:
                $low_col = $this->dot->settings['indi_stripe_age_low_col'];
                $high_col = $this->dot->settings['indi_stripe_age_high_col'];
                $low_age = $this->dot->settings['indi_stripe_age_low'];
                $high_age = $this->dot->settings['indi_stripe_age_high'];
                break;
        }
        $range = $high_age - $low_age;
        if ($range == 0) $range = 0.01;
        $ratio = min(max($age-$low_age, 0), $range)/$range;
        $colour = new Colour($low_col);
        return $colour->mergeWithColour($high_col, $ratio);
    }
}