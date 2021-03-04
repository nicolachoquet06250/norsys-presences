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

	$email = $user['email'];

	$agency = $user['agency'];

	$week = get_week_extremity_days(date('W'), date('Y'));

	$start = date('d', strtotime($week['first_day']));
	$end = date('d', strtotime($week['last_day']));
	$month = $monthes[intval(date('n', strtotime($week['first_day'])))];

	$agency_users = [];

	try {
		$db = getDB();

		/*$request = $db->prepare('SELECT * FROM `users` WHERE agency_id = ( SELECT id FROM agencies WHERE name = :agency )', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
		$request->execute([ 'agency' => $agency ]);
		$agency_users = $request->fetchAll(PDO::FETCH_ASSOC);*/

		$agency_users = [
			[
				'email' => $email
			]
		];

		$request = $db->prepare('INSERT INTO recap (`user_id`, `agency_id`, `object`, `content`) VALUES(:user_id, (SELECT id FROM agencies WHERE name = :agency), :object, :content)', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
		$request->execute([
			'user_id' => $user['id'],
			'agency' => $user['agency'],
			'object' => $object,
			'content' => $body['html']
		]);

		$html = '
	<!DOCTYPE html>
	<html lang="en">
	<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>Norsys Sophia | Fiche de présence</title>
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" 
				rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" 
				crossorigin="anonymous">
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.23.0/ui/trumbowyg.min.css" />
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.19.1/plugins/emoji/ui/trumbowyg.emoji.css" />
	</head>
	<body>' . str_replace(['%start%', '%end%', '%month%'], [$start, $end, $month], $body['html']) . '</body>
	</html>';

		$object = 'Récap Hebdo Sophia ' . date('d/m/Y');

		$mails = [];

		foreach ($agency_users as $user) {
			$mails[$user['email']] = smtpMailer($user['email'], $html, $object);
		}

		foreach ($mails as $mail) {
			if ($mail !== true) {
				exit(json_encode([
					'error' => true,
					'message' => $mail
				]));
			}
		}

		echo json_encode([
			'error' => false
		]);
	} catch (Exception $e) {
		
	}

	//echo str_replace(['%start%', '%end%', '%month%'], [$start, $end, $month], $body['html']);

}, 'post');

Route::add('/api/recap/([0-9]+)', function() {}, 'put');

Route::add('/api/recap/([0-9]+)', function() {}, 'delete');
