<?php

namespace vendor\WebtreesModules\gvexport;
use Exception;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Handles GVExport custom API calls from front end
 */
class ApiHandler
{
    /**
     * @var array
     */
    var array $response_data = array();

    /**
     * Convert response data into a data stream for returning response
     *
     * @return mixed
     */
    public function getResponse() {
        $stream = app(StreamFactoryInterface::class)->createStream(json_encode($this->response_data));
        $response_factory = app(ResponseFactoryInterface::class);
        return $response_factory->createResponse()
            ->withBody($stream)
            ->withHeader('Content-Type', "application/json");
    }

    /**
     * Process the $request data and return data stream of result
     *
     * @param $request
     * @param $module
     * @param $tree
     * @return mixed
     */
    public function handle($request, $module, $tree) {
        $json_data = Validator::parsedBody($request)->string('json_data');
        $json = json_decode($json_data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $type = FormSubmission::isNameStringValid($json['type']) ? $json['type'] : "";
            switch ($type) {
                case "save_settings":
                    $this->saveSettings($module, $request, $json, $tree);
                    break;
                case "get_settings":
                    $this->getSettings($json, $module, $tree);
                    break;
                case "delete_settings":
                    $this->deleteSettings($module, $json, $tree);
                    break;
                case "is_logged_in":
                    $this->isLoggedIn();
                    break;
                case "get_tree_name":
                    $this->getTreeName($tree);
                    break;
                case "get_saved_settings_link":
                    $this->getSavedSettingsLink($json, $module, $tree);
                    break;
                case "load_settings_token":
                    $this->loadSettingsToken($json, $module, $tree);
                    break;
                case "revoke_saved_settings_link":
                    $this->revokeSettingsToken($json, $module, $tree);
                    break;
                case "add_my_favorite":
                    $this->addUserFavourite($json, $module, $tree);
                    break;
                case "add_tree_favorite":
                    $this->addTreeFavourite($json, $module, $tree);
                    break;
                case "get_help":
                    $this->getHelp($module, $json);
                    break;
                default:
                    $this->response_data['success'] = false;
                    $this->response_data['json'] = $json_data;
                    $this->response_data['errorMessage'] = I18N::translate('Invalid request') . ": " . $type;
            }
        } else {
            $this->response_data['success'] = false;
            $this->response_data['json'] = $json_data;
            $this->response_data['errorMessage'] = I18N::translate('Invalid JSON') . ": " . json_last_error_msg();
        }

        return $this->getResponse();
    }

    /**
     *  Set a failure response to the API instance
     *
     * @param string $error
     * @param string $code
     * @return void
     */
    private function setFailResponse(string $error, string $code = "") {
        $this->response_data['success'] = false;
        $this->response_data['errorMessage'] = ($code === '' ? '' : $code . ": ") . I18N::translate($error);
    }

    /**
     * One of the API functions, this one is to save the provided settings
     *
     * @param $module
     * @param $request
     * @param $json
     * @param $tree
     * @return void
     */
    public function saveSettings($module, $request, $json, $tree): void
    {
        $vars = Validator::parsedBody($request)->array('vars');
        $formSubmission = new FormSubmission();
        $vars = $formSubmission->load($vars, $module);
        if (isset($json['settings_id']) && ctype_alnum($json['settings_id']) && !in_array($json['settings_id'], [Settings::ID_ALL_SETTINGS, Settings::ID_MAIN_SETTINGS])) {
            if ($this->doesSettingsIdBelongsToUser($module, $tree, $json['settings_id'])) {
                $id = $json['settings_id'];
            }
        }
        $settings = new Settings();
        if (!isset($json['main']) || $json['main']) {
            $id = Settings::ID_MAIN_SETTINGS;
        } else if (!isset($id) || $id == '') {
            $id = $settings->newSettingsId($module, $tree);
        }

        if ($id != "") {
            $this->response_data['settings_id'] = $id;
            $this->response_data['success'] = $settings->saveUserSettings($module, $tree, $vars, $id);
        } else {
            $this->setFailResponse('Failed to assign new settings ID', 'E10');
        }
    }


    /**
     * Return whether the settings ID belongs to the current user
     *
     * @param $module
     * @param $tree
     * @param $settings_id
     * @return bool
     */
    private function doesSettingsIdBelongsToUser($module, $tree, $settings_id): bool
    {
        $settings = new Settings();
        try {
            $settings->getSettingsJson($module, $tree, $settings_id);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * One of the API functions, this one is to load the requested settings
     *
     * @param $json
     * @param $module
     * @param $tree
     * @return void
     */
    public function getSettings($json, $module, $tree): void
    {
        if (isset($json['settings_id']) && (ctype_alnum($json['settings_id']) || in_array($json['settings_id'], [Settings::ID_ALL_SETTINGS, Settings::ID_MAIN_SETTINGS]))) {
            $settings = new Settings();
            try {
                $this->response_data['settings'] = ($json['settings_id'] == Settings::ID_ALL_SETTINGS ? $settings->getAllSettingsJson($module, $tree) : $settings->getSettingsJson($module, $tree, $json['settings_id']));
                $this->response_data['success'] = true;
            } catch (Exception $e) {
                $this->setFailResponse('Invalid JSON', 'E9');
            }
        } else {
            $this->setFailResponse('Invalid settings ID', 'E6');
        }
    }

    /**
     * One of the API functions, this one is to generate a settings link for sharing
     *
     * @param $json
     * @param $module
     * @param $tree
     * @return void
     */
    public function getSavedSettingsLink($json, $module, $tree): void
    {
        if (isset($json['settings_id']) && (ctype_alnum($json['settings_id']))) {
            $settings = new Settings();
            $link = $settings->getSettingsLink($module, $tree, $json['settings_id']);
            if ($link['success']) {
                $this->response_data['url'] = $link['url'];
                $this->response_data['success'] = true;
            } else {
                $this->setFailResponse($link['error'], 'E11');
            }
        } else {
            $this->setFailResponse('Invalid settings ID', 'E3');
        }
    }

    /**
     * One of the API functions, this one is to load settings from a token provided by a shared setting link
     *
     * @param $json
     * @param $module
     * @param $tree
     * @return void
     */
    public function loadSettingsToken($json, $module, $tree): void
    {
        if (isset($json['token']) && (ctype_alnum($json['token']))) {
            $settings = new Settings();
            try {
                $this->response_data['settings'] = $settings->loadSettingsToken($module, $tree, $json['token']);
                $this->response_data['success'] = true;
            } catch (Exception $e) {
                $this->setFailResponse('Invalid settings ID', 'E7');
            }
        } else {
            $this->setFailResponse('Invalid settings ID', 'E4');
        }
    }

    /**
     * One of the API functions, this one is to revoke a settings token, in the
     * case that a shared settings link has been deleted by the user
     *
     * @param $json
     * @param $module
     * @param $tree
     * @return void
     */
    private function revokeSettingsToken($json, $module, $tree)
    {
        if (isset($json['token']) && (ctype_alnum($json['token']))) {
            $settings = new Settings();
            $link = $settings->revokeSettingsToken($module, $tree, $json['token']);
            if ($link) {
                $this->response_data['success'] = true;
            } else {
                $this->setFailResponse('Invalid', 'E2');
            }
        } else {
            $this->setFailResponse('Invalid settings ID', 'E5');
        }
    }

    /**
     * One of the API functions, this one is to delete one of the named settings
     * records saved by a user
     *
     * @param $module
     * @param $json
     * @param $tree
     * @return void
     */
    public function deleteSettings($module, $json, $tree): void
    {
        if (isset($json['settings_id']) && ctype_alnum($json['settings_id']) && !in_array($json['settings_id'], [Settings::ID_ALL_SETTINGS, Settings::ID_MAIN_SETTINGS])) {
            if (Settings::isUserLoggedIn()) {
                $settings = new Settings();
                $settings->deleteUserSettings($module, $tree, $json['settings_id']);
                $this->response_data['success'] = true;
            } else {
                // Is user is not logged in, we should never have got this far
                $this->setFailResponse('Invalid', 'E12');
            }
        } else {
            $this->setFailResponse('Invalid settings ID', 'E8');
        }
    }

    /**
     * One of the API functions, this indicates if the user is currently logged in
     *
     * @return void
     */
    private function isLoggedIn()
    {
        if (Settings::isUserLoggedIn()) {
            $this->response_data['loggedIn'] = true;
        } else {
            $this->response_data['loggedIn'] = false;
        }
        $this->response_data['success'] = true;
    }

    /**
     * One of the API functions, this provides the name of the tree
     * @param $tree
     * @return void
     */
    private function getTreeName($tree)
    {
        $this->response_data['treeName'] = e($tree->name());
        $this->response_data['success'] = true;
    }

    /**
     * One of the API functions, it adds a User favourite to webtrees
     *
     * @param $json
     * @param $module
     * @param $tree
     * @return void
     */
    private function addUserFavourite($json, $module, $tree)
    {
        $this->addFavourite($json, $module, $tree, Favourite::TYPE_USER_FAVOURITE);
    }

    /**
     * One of the API functions, it adds a Tree favourite to webtrees
     *
     * @param $json
     * @param $module
     * @param $tree
     * @return void
     */
    private function addTreeFavourite($json, $module, $tree)
    {
        if (Auth::isManager($tree)) {
            $this->addFavourite($json, $module, $tree, Favourite::TYPE_TREE_FAVOURITE);
        }
    }

    /**
     * Does the heavy lifting of adding a favourite of the provided type
     *
     * @param $json
     * @param $module
     * @param $tree
     * @param $type
     * @return void
     */
    private function addFavourite($json, $module, $tree, $type): void
    {
        if (isset($json['settings_id']) && (ctype_alnum($json['settings_id']))) {
            $settings = new Settings();
            $link = $settings->getSettingsLink($module, $tree, $json['settings_id']);
            $name = $settings->getSettingsName($module, $tree, $json['settings_id']);
            if ($link['success']) {
                $favourite = new Favourite($type);
                if ($favourite->addFavourite($tree, $link['url'], $name)) {
                    $this->response_data['success'] = true;
                } else {
                    $this->setFailResponse('Invalid', 'E13');
                }
            } else {
                $this->setFailResponse($link['error'], 'E11');
            }
        } else {
            $this->setFailResponse('Invalid settings ID', 'E14');
        }
    }

    /**
     * Retrieve the help information from appropriate view file
     *
     * @param $module
     * @param $json
     * @return void
     */
    private function getHelp($module, $json)
    {
        $help = new Help();
        $this->response_data['success'] = true;
        if ($help->helpExists($json['help_name'])) {
            $this->response_data['help'] = view($module->name() . '::MainPage/Help/' . $help->getHelpLocation($json['help_name']) . $json['help_name'],['module' => $module]);
        } else {
            // API call successful, even though help information not found
            $this->response_data['help'] = view($module->name() . '::MainPage/Help/' . $help->getHelpLocation(Help::NOT_FOUND) . Help::NOT_FOUND,[]);
        }
    }

}