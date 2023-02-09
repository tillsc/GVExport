<?php

namespace vendor\WebtreesModules\gvexport;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
class ApiHandler
{
    var array $response_data = array();

    public function getResponse() {
        $stream = app(StreamFactoryInterface::class)->createStream(json_encode($this->response_data));
        $response_factory = app(ResponseFactoryInterface::class);
        return $response_factory->createResponse()
            ->withBody($stream)
            ->withHeader('Content-Type', "application/json");
    }

    public function handle($request, $module, $tree) {
        $json_data = Validator::parsedBody($request)->string('json_data');
        $json = json_decode($json_data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $type = FormSubmission::nameStringValid($json['type']) ? $json['type'] : "";
            switch ($type) {
                case "save_settings":
                    $this->saveSettings($module, $request, $json, $tree);
                    break;
                case "get_settings":
                    $this->getSettings($json, $module, $tree, $json_data);
                    break;
                case "delete_settings":
                    $this->deleteSettings($module, $json, $tree, $json_data);
                    break;
                case "is_logged_in":
                    $this->isLoggedIn();
                    break;
                case "get_tree_name":
                    $this->getTreeName($tree);
                    break;
                case "get_saved_settings_link":
                    $this->getSavedSettingsLink($json, $module, $tree, $json_data);
                    break;
                case "load_settings_token":
                    $this->loadSettingsToken($json, $module, $tree, $json_data);
                    break;
                case "revoke_saved_settings_link":
                    $this->revokeSettingsToken($json, $module, $tree, $json_data);
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
    }

    private function addFailResponse($error, $code = "") {
        $this->response_data['success'] = false;
        $this->response_data['errorMessage'] = ($code === '' ? '' : $code . ": ") . I18N::translate($error);
    }

    public function saveSettings($module, $request, $json, $tree): void
    {
        $vars = Validator::parsedBody($request)->array('vars');
        $formSubmission = new FormSubmission();
        $vars = $formSubmission->load($vars);
        if (isset($json['settings_id']) && ctype_alnum($json['settings_id']) && !in_array($json['settings_id'], [Settings::ID_ALL_SETTINGS, Settings::ID_MAIN_SETTINGS])) {
            if ($this->checkIdBelongsToUser($module, $tree, $json['settings_id'])) {
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
            $this->addFailResponse('Failed to assign new settings ID', 'E10');
        }
    }

    public function getSettings($json, $module, $tree, string $json_data): void
    {
        if (isset($json['settings_id']) && (ctype_alnum($json['settings_id']) || in_array($json['settings_id'], [Settings::ID_ALL_SETTINGS, Settings::ID_MAIN_SETTINGS]))) {
            $settings = new Settings();
            $this->response_data['settings'] = ($json['settings_id'] == Settings::ID_ALL_SETTINGS ? $settings->getAllSettingsJson($module, $tree) : $settings->getSettingsJson($module, $tree, $json['settings_id']));
            $this->response_data['success'] = true;
        } else {
            $this->addFailResponse('Invalid settings ID', 'E6');
        }
    }
    public function getSavedSettingsLink($json, $module, $tree, string $json_data): void
    {
        if (isset($json['settings_id']) && (ctype_alnum($json['settings_id']))) {
            $settings = new Settings();
            $link = $settings->getSettingsLink($module, $tree, $json['settings_id']);
            if ($link['success']) {
                $this->response_data['url'] = $link['url'];
                $this->response_data['success'] = true;
            } else {
                $this->addFailResponse($link['error'], 'E11');
            }
        } else {
            $this->addFailResponse('Invalid settings ID', 'E3');
        }
    }
    public function loadSettingsToken($json, $module, $tree, string $json_data): void
    {
        if (isset($json['token']) && (ctype_alnum($json['token']))) {
            $settings = new Settings();
            try {
                $this->response_data['settings'] = $settings->loadSettingsToken($module, $tree, $json['token']);
                $this->response_data['success'] = true;
            } catch (\Exception $e) {
                $this->addFailResponse('Invalid settings ID', 'E7');
            }
        } else {
            $this->addFailResponse('Invalid settings ID', 'E4');
        }
    }

    private function revokeSettingsToken($json, $module, $tree, string $json_data)
    {
        if (isset($json['token']) && (ctype_alnum($json['token']))) {
            $settings = new Settings();
            $link = $settings->revokeSettingsToken($module, $tree, $json['token']);
            if ($link) {
                $this->response_data['success'] = true;
            } else {
                $this->addFailResponse('Invalid', 'E2');
            }
        } else {
            $this->addFailResponse('Invalid settings ID', 'E5');
        }
    }

    public function deleteSettings($module, $json, $tree, string $json_data): void
    {
        if (isset($json['settings_id']) && ctype_alnum($json['settings_id']) && !in_array($json['settings_id'], [Settings::ID_ALL_SETTINGS, Settings::ID_MAIN_SETTINGS])) {
            if (Settings::isUserLoggedIn()) {
                $settings = new Settings();
                $settings->deleteUserSettings($module, $tree, $json['settings_id']);
                $this->response_data['success'] = true;
            } else {
                // Is user is not logged in, we should never have got this far
                $this->addFailResponse('Invalid', 'E12');
            }
        } else {
            $this->addFailResponse('Invalid settings ID', 'E8');
        }
    }

    private function isLoggedIn()
    {
        if (Settings::isUserLoggedIn()) {
            $this->response_data['loggedIn'] = true;
        } else {
            $this->response_data['loggedIn'] = false;
        }
        $this->response_data['success'] = true;
    }
    private function getTreeName($tree)
    {
        $this->response_data['treeName'] = e($tree->name());
        $this->response_data['success'] = true;
    }

    private function checkIdBelongsToUser($module, $tree, $settings_id): bool
    {
        $settings = new Settings();
        try {
            $settings->getSettingsJson($module, $tree, $settings_id);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}