<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

date_default_timezone_set('Europe/Moscow');


/* Main includes */
require_once(dirname(__FILE__) . '/engine/config.php');

/* Check for cinfig */
if (!defined('SENGIN')) {
    echo "Not installed! Use config to set all properties";
    exit;
} else {
    /* Include Sengin */
    require_once(SENGIN . '/Application.class.php');
}

if (empty($_REQUEST['request']) || empty($_REQUEST['action'])) {
    @header('HTTP/1.0 400 Bad Request', true, 400);
    exit;
}

$request = $_REQUEST['request'];
$action = $_REQUEST['action'];

/* Create Application with modules */
$App = Application::getInstance();
if (!$App->AppInit($Config, $request)) {
    @header('HTTP/1.0 400 Bad Request', true, 400);
    exit;
}

if (is_object($App->Controller)) {
    if (is_callable(array($App->Controller, $action))) {
        @header('Content-Type: application/json');
        call_user_func_array(array($App->Controller, $action), array($_REQUEST));
    } else {
        @header('HTTP/1.0 400 Bad Request', true, 400);
    }
} else {
    @header('HTTP/1.0 400 Bad Request', true, 400);
}
