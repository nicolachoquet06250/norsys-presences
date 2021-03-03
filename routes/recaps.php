<?php

use Steampixel\Route;

Route::add('/api/recaps', function() {}, 'get');

Route::add('/api/recap/([0-9]+)', function($id) {}, 'get');

Route::add('/api/recap', function() {}, 'post');

Route::add('/api/recap/([0-9]+)', function() {}, 'put');

Route::add('/api/recap/([0-9]+)', function() {}, 'delete');