<?php

$sqlitePath = __DIR__ . '/../database/database.sqlite';
if (!file_exists($sqlitePath)) {
    if (!is_dir(dirname($sqlitePath))) {
        mkdir(dirname($sqlitePath), 0755, true);
    }
    touch($sqlitePath);
    
    // Automatically triggers migrations seamlessly in the background on boot
    exec('cd ' . escapeshellarg(__DIR__ . '/..') . ' && php artisan migrate --force');
}
// ------------------------------------------------

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
