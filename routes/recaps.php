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

Route::add('/api/recaps/([^\/]+)', function($token) {
	try {
		if (empty($token)) {
			throw new Exception('Authentification invalide');
		}
		$token = base64_decode($token);
		if ($token) {
			$token = json_decode($token, true);

			if (json_last_error()) {
				throw new Exception(json_last_error_msg());
			}
		}

		$db = getDB();
		$request = $db->prepare('SELECT recap.id recap_id, 
										DATE_FORMAT(creation_date, \'%Y-%m-%d\') creation_date, 
										content, object, 
										users.firstname, users.lastname, users.email 
											FROM recap INNER JOIN users 
												ON recap.user_id = users.id 
												WHERE recap.agency_id = (
													SELECT id FROM agencies WHERE name = :agency
												)', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
		$request->execute([ 'agency' => $token['agency'] ]);
		$result = $request->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode($result);
	} catch (Exception $e) {
		exit(json_encode([
			'error' => true,
			'message' => $e->getMessage()
		]));
	}
}, 'get');

Route::add('/api/recap/([0-9]+)/([^\/]+)', function($id, $token) {
	$monthes = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'septembre', 'Octobre', 'Novembre', 'Décembre'];

	try {
		if (empty($token)) {
			throw new Exception('Authentification invalide');
		}
		$token = base64_decode($token);
		if ($token) {
			$token = json_decode($token, true);

			if (json_last_error()) {
				throw new Exception(json_last_error_msg());
			}
		}

		$db = getDB();
		$request = $db->prepare('SELECT recap.id recap_id, 
										DATE_FORMAT(creation_date, \'%Y-%m-%d\') creation_date, 
										content, object, 
										users.firstname, users.lastname, users.email 
											FROM recap INNER JOIN users 
												ON recap.user_id = users.id 
												WHERE recap.agency_id = (
													SELECT id FROM agencies WHERE name = :agency
												) AND recap.id = :id', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
		$request->execute([
			'agency' => $token['agency'],
			'id' => $id
		]);
		$result = $request->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result) === false) $result = $result[0];

		$week = get_week_extremity_days(date('W', strtotime($result['creation_date'])), date('Y', strtotime($result['creation_date'])));

		$start = date('d', strtotime($week['first_day']));
		$end = date('d', strtotime($week['last_day']));
		$month = $monthes[intval(date('n', strtotime($week['first_day'])))];

		$result['vars'] = [
			'start' => $start,
			'end' => $end,
			'month' => $month
		];

		echo json_encode($result);
	} catch (Exception $e) {
		exit(json_encode([
			'error' => true,
			'message' => $e->getMessage()
		]));
	}
}, 'get');

Route::add('/api/recap', function() {
	$monthes = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'septembre', 'Octobre', 'Novembre', 'Décembre'];

	$body = getBody();

	if (empty($body['html']) === false) {
		$token = $body['token'];
		$user = json_decode(base64_decode($token), true);

		$email = $user['email'];

		$agency = $user['agency'];

		$week = get_week_extremity_days(date('W'), date('Y'));

		$start = date('d', strtotime($week['first_day']));
		$end = date('d', strtotime($week['last_day']));
		$month = $monthes[intval(date('n', strtotime($week['first_day'])))];

		$object = 'Récap Hebdo Sophia ' . date('d/m/Y');

		$agency_users = [];

		try {
			$db = getDB();

			$request = $db->prepare('SELECT mailing_list FROM `agencies` WHERE name = :agency', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
			$request->execute([ 'agency' => $agency ]);
			$agency_users = $request->fetch(PDO::FETCH_ASSOC);

			if (!empty($agency_users) && $agency_users['mailing_list'] === null) {
				$request = $db->prepare('SELECT * FROM `users` WHERE agency_id = ( SELECT id FROM agencies WHERE name = :agency )', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
				$request->execute([ 'agency' => $agency ]);
				$agency_users = $request->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$agency_users = [
					[
						'email' => $agency_users['mailing_list']
					]
				];
			}
			
			// DEBUG
			/*$agency_users = [
				[
					'email' => $email
				]
			];*/

			$request = $db->prepare('INSERT INTO recap (`user_id`, `agency_id`, `object`, `content`) VALUES(:user_id, (SELECT id FROM agencies WHERE name = :agency), :object, :content)', [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
			$request->execute([
				'user_id' => $user['id'],
				'agency' => $user['agency'],
				'object' => $object,
				'content' => $body['html']
			]);

			$html = '
		<!DOCTYPE HTML PUBLIC "~//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
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

			$mails = [];

			$agency_users = array_map(function($c) {
				return $c['email'];
			}, $agency_users);

			$mail = smtpMailer($agency_users, $html, $object);

			if ($mail !== true) {
				exit(json_encode([
					'error' => true,
					'message' => $mail
				]));
			}

			echo json_encode([
				'error' => false
			]);
		} catch (Exception $e) {
			exit(json_encode([
				'error' => true,
				'message' => $e->getMessage()
			]));
		}
	} else {
		exit(json_encode([
			'error' => true,
			'message' => 'Vous devez remplire le message avant de l\'envoyer...'
		]));
	}

}, 'post');

Route::add('/api/recap/save-template', function() {
	$body = getBody();

	if (empty($body['html']) === false) {
		file_put_contents(__DIR__.'/../views/recap_templates/hebdo.html', $body['html']);
	}
	echo json_encode([
		'error' => false
	]);
}, ['put', 'post']);
