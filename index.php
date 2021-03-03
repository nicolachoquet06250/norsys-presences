<?php

ini_set('display_errors', '1');

require __DIR__.'/vendor/autoload.php';

use Steampixel\Route;

/* Functions */
include __DIR__.'/functions.php';

$dir = opendir(__DIR__.'/routes');
while (($elem = readdir($dir)) !== false) {
    if ($elem !== '.' && $elem !== '..' && is_file(__DIR__.'/routes/'.$elem)) {
        include __DIR__.'/routes/'.$elem;
    }
}

/* Main */

Route::run('/');
