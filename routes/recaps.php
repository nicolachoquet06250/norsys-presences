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
	$monthes = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'septembre', 'Octobre', 'Novembre', 'Décembre'];

	$body = getBody();
	$token = $body['token'];
	$user = json_decode(base64_decode($token), true);

	$week = get_week_extremity_days(date('W'), date('Y'));

	$start = date('d/m/Y', strtotime($week['first_day']));
	$end = date('d/m/Y', strtotime($week['last_day']));
	$month = $monthes[intval(date('n', strtotime($week['first_day'])))];

	echo $week['first_day']. ' ' . $week['last_day'] . ' ' . date('m', strtotime($week['first_day'])) . str_replace(['%start%', '%end%', '%month%'], [$start, $end, $month], $body['html']);
}, 'post');

Route::add('/api/recap/([0-9]+)', function() {}, 'put');

Route::add('/api/recap/([0-9]+)', function() {}, 'delete');
