<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/storage/error.log');

error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/Project/public');
define('ASSETS_URL', BASE_URL . '/assets');
define('USERS_JSON', 'https://dummyjson.com/users');

define('CONTROLLERS_PATH', __DIR__ . '/controllers');
define('MODELS_PATH', __DIR__ . '/models');
define('VIEWS_PATH', __DIR__ . '/views');
define('STORAGE_PATH', __DIR__ . '/storage');

spl_autoload_register(function ($class_name)
{
    $file_path = sprintf("%s/%s.php", MODELS_PATH, str_replace("\\", '/', $class_name));
    if (is_readable($file_path))
    {
        include_once $file_path;
    }
});

spl_autoload_register(function ($class_name)
{
    $file_path = sprintf("%s/%s.php", CONTROLLERS_PATH, str_replace("\\", '/', $class_name));
    if (is_readable($file_path))
    {
        include_once $file_path;
    }
});

$config = require_once __DIR__ . '/config.php';

require_once __DIR__ . '/router.php';