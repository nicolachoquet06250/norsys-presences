<?php

use Steampixel\Route;

Route::add('/api/recap/upload', function() {
    if (empty($_FILES['image']) === false) {
        if (!is_dir(__DIR__.'/../assets/recaps/images')) {
		mkdir(__DIR__.'/../assets/recaps/images', 0777, true);
	}
	$path = __DIR__.'/../assets/recaps/images/'.basename($_FILES['image']['name']);
	if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
		echo json_encode([
			'success' => true,
			'file' => 'https://'.$_SERVER['SERVER_NAME'].'/assets/recaps/images/'.basename($_FILES['image']['name'])
		]);
	} else {
		echo json_encode([ 'success' => false, 'message' => "Il y a eu un problème lors de l'upload" ]);
	}
    }
}, 'post');

Route::add('/api/recaps', function() {}, 'get');

Route::add('/api/recap/([0-9]+)', function($id) {}, 'get');

Route::add('/api/recap', function() {
	echo getBody()['html'];
}, 'post');

Route::add('/api/recap/([0-9]+)', function() {}, 'put');

Route::add('/api/recap/([0-9]+)', function() {}, 'delete');
