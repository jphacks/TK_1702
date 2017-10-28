<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

define('CH_BASE_DIR', __DIR__ . '/..');
define('CH_WEBROOT_PATH', CH_BASE_DIR . '/public');
define('CH_TEMPLATE_PATH', CH_BASE_DIR . '/templates');


require_once __DIR__ . '/../vendor/autoload.php';

// this locks file and make requests all sequential?
//session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Chat\Slim\ExposedApp($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
