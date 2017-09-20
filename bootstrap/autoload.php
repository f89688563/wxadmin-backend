<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so we do not have to manually load any of
| our application's PHP classes. It just feels great to relax.
|
*/

$functions_path = __DIR__ . '/../app/Common/functions.php';
if (file_exists($functions_path))
{
    require $functions_path;
}

require_once __DIR__.'/../vendor/autoload.php';
