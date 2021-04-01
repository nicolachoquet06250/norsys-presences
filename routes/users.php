<?php
use Steampixel\Route;

Route::add('/api/user/register', function() {
	$body = getBody();
	if (empty($body['firstname']) || empty($body['lastname']) || empty($body['password']) || empty($body['agency'])) {
	  exit(json_encode([
			'error' => true,
			'message' => 'Veuillez remplire les champs'
		]));
	}
	$lastname = str_replace(' ', '', $body['lastname']);
	$pseudo = strtolower(substr($body['firstname'], 0, 1).$lastname);
	if (strstr($body['firstname'], '-')) {
	  $pseudo = substr(explode('-', $body['firstname'])[0], 0, 1).substr(explode('-', $body['firstname'])[1], 0, 1).$lastname;
	}
	if (strstr($body['firstname'], ' ')) {
	  $pseudo = substr(explode(' ', $body['firstname'])[0], 0, 1).substr(explode(' ', $body['firstname'])[1], 0, 1).$lastname;
	}
	$body['email'] = strtolower($pseudo).'@norsys.fr';
	$body['password'] = sha1($body['password']);
	
	try {
		$db = getDB();
		$request = $db->prepare('SELECT id FROM `users` WHERE email=:email', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute(['email' => $body['email']]);
		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
		
		if (!empty($arr)) {
		  throw new Exception('Un compte avec cet email existe déjà');
		}
		
		$request = $db->prepare('INSERT INTO `users` (`firstname`, `lastname`, `email`, `password`, `agency_id`) VALUES(:firstname, :lastname, :email, :password, :agency)', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute($body);
	}
	catch (Exception $e) {
		exit(json_encode([
			'error' => true,
			'message' => $e->getMessage()
		]));
	}
	echo json_encode($body);
}, 'post');

Route::add('/api/user/password', function() {
  $body = getBody();
  if (strtolower($_SERVER['REQUEST_METHOD']) === 'put' || (isset($body['method']) && strtolower($body['method']) === 'put')) {
  	$user_id = $body['user_id'];
  	$password = sha1($body['password']);
  
  	try {
    	$db = getDB();
    
    	$request = $db->prepare('SELECT password FROM `users` WHERE id=:user_id', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute([
		  'user_id' => $user_id
		]);
		
		$result = $request->fetch(PDO::FETCH_ASSOC);
		
		if (is_array($result) && $result['password'] === $password) {
		  throw new Exception('Votre nouveau mot de passe ne doit pas être identique à l\'ancien');
		}
		
    	$request = $db->prepare('UPDATE `users` SET password=:password WHERE id=:user_id', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute([
		  'password' => $password,
		  'user_id' => $user_id
		]);
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
		http_response_code(400);

		exit(json_encode([
			'error' => true,
			'code' => 401,
			'message' => 'BAD REQUEST'
		]));
  }
}, ['put', 'post']);