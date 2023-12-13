<?php

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Module\FamilyTreeFavoritesModule;
use Fisharebest\Webtrees\Module\UserFavoritesModule;
use Fisharebest\Webtrees\Tree;

/**
 * Container for shared note style data
 */
class SharedNoteList
{
    private array $xref_colour = [];


    /**
     * @param $json
     */
    public function __construct($json)
    {
        $data = json_decode($json, true);
        if ($data !== null) {
            foreach ($data as $element) {
                $note_xref = $element['xref'];
                $bg_col = $element['bg_col'];
            }
        }
    }

    public function indiHasSharedNote($xref): bool
    {
        return true;
    }

    public function getSharedNoteColour($xref): bool
    {
        return '#000000';
    }
}