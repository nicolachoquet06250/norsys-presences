<?php

ini_set('display_errors', '1');

require __DIR__.'/vendor/autoload.php';

use Steampixel\Route;

/* Functions */
include __DIR__.'/functions.php';

/* Front */
include __DIR__.'/routes/front.php';

/* Login */
include __DIR__.'/routes/login.php';

/* Users */
include __DIR__.'/routes/users.php';

/* Presences */
include __DIR__.'/routes/presences.php';

/* Agencies */
include __DIR__.'/routes/agencies.php';

/* Search */
include __DIR__.'/routes/search.php';

/* Export */
include __DIR__.'/routes/export.php';

/* Calendar */
include __DIR__.'/routes/calendar.php';

/* Main */

Route::run('/');
