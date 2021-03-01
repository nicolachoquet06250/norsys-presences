<?php
use Steampixel\Route;

Route::add('/api/user/login', function () {
  $body = getBody();
  $body['password'] = sha1($body['password']);
  
  try {
    $db = getDB();
    $request = $db->prepare('SELECT `users`.id id, firstname, lastname, email, `agencies`.name agency FROM `users` INNER JOIN `agencies` ON `users`.agency_id = `agencies`.id WHERE email=:email AND password=:password', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
    $request->execute($body);
    $arr = $request->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($arr)) {
      $result = $arr[0];
    } else {
      throw new Exception('Aucun utilisateur ne correspond à ces identifiants');
    }
    
    $result['token'] = base64_encode((json_encode($result)));
    
    echo json_encode($result);
  }
  catch(Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, 'post');