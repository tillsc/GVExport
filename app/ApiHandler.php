<?php

namespace vendor\WebtreesModules\gvexport;
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

    public function handle($json_data, $module, $tree) {
        $request = json_decode($json_data, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            switch ($request['type']) {
                case "save_settings":
                    $formSubmission = new FormSubmission();
                    $vars = $formSubmission->load($_REQUEST['vars']);
                    $settings = new Settings();
                    $settings->saveUserSettings($tree, $vars);
                    $this->response_data['settings'] = $settings->getSettingsJson($module, $tree);
                    $this->response_data['success'] = true;
                    break;
                case "get_settings":
                    $settings = new Settings();
                    $this->response_data['settings'] = $settings->getSettingsJson($module, $tree);
                    $this->response_data['success'] = true;
                    break;
                default:
                    $this->response_data['success'] = false;
                    $this->response_data['error'] = "invalid request. JSON: " . $json_data;
                    return false;
            }
        } else {
            $this->response_data['success'] = false;
            $this->response_data['error'] = "invalid json: " . json_last_error_msg() . "JSON: " . $json_data;
        }
    }
}