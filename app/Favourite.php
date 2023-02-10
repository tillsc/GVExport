<?php

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Module\FamilyTreeFavoritesModule;
use Fisharebest\Webtrees\Module\UserFavoritesModule;
use Fisharebest\Webtrees\Tree;

class Favourite
{
    public const TYPE_USER_FAVOURITE = 'USER_FAVOURITE';
    public const TYPE_TREE_FAVOURITE = "TREE_FAVOURITE";
    private string $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function addFavourite($tree, $url, $title): bool
    {
        switch ($this->type) {
            case self::TYPE_USER_FAVOURITE:
                return $this->addUserFavourite($tree, $url, $title);
            case self::TYPE_TREE_FAVOURITE:
                return $this->addTreeFavourite($tree, $url, $title);
            default:
                return false;
        }
    }

    private function addUserFavourite(Tree $tree, string $url, string $title): bool
    {
        $note = "";
        $user = Auth::user();
        $favorite = function ($tree, $user, $url, $title, $note) {
            return app(UserFavoritesModule::class)->addUrlFavorite($tree, $user, $url, $title, $note);
        };
        $favorite->call(app(UserFavoritesModule::class), $tree, $user, $url, $title, $note);
        return true;
    }

    private function addTreeFavourite(Tree $tree, string $url, string $title): bool
    {
        $note = "";
        $favorite = function ($tree, $url, $title, $note) {
            return app(FamilyTreeFavoritesModule::class)->addUrlFavorite($tree, $url, $title, $note);
        };
        $favorite->call(app(FamilyTreeFavoritesModule::class), $tree, $url, $title, $note);
        return true;
    }
}