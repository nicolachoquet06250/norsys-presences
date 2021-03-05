<?php

ini_set('display_errors', '1');

require __DIR__.'/vendor/autoload.php';

/* Functions */
include __DIR__.'/functions.php';

use Steampixel\Route;

if (!file_exists(__DIR__.'/config.json')) {
    exit('Un fichier de configuration doit être présent !!');
}

init_config();

$dir = opendir(__DIR__.'/routes');
while (($elem = readdir($dir)) !== false) {
    if ($elem !== '.' && $elem !== '..' && is_file(__DIR__.'/routes/'.$elem)) {
        include __DIR__.'/routes/'.$elem;
    }
}

/* Main */

Route::run('/');
