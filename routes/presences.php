<?php
use Steampixel\Route;

Route::add('/api/presences', function () {
  try {
    $token = $_GET['token'];
    $user = base64_decode($token);
    $user = json_decode($user, true);
  }
  catch(Exception $e) {
    exit(json_encode([
      'error' => true,
      'authent' => true,
      'message' => $e->getMessage()
    ]));
  }
  
  try {
    $db = getDB();
    $request = $db->query('SELECT firstname, lastname, email, arrival_date, departure_date FROM `presences` INNER JOIN `users` ON `presences`.user_id = `users`.id');
    $arr = $request->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($arr);
  }
  catch (Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, 'get');

Route::add('/api/presences/today', function () {
  try {
    if (empty($_GET['token'])) {
      throw new Exception('Identification invalide');
    }
    $token = $_GET['token'];
    $user = base64_decode($token);
    
    if ($user === false) {
      throw new Exception('Token invalide');
    }
    
    $user = json_decode($user, true);
    
    if (empty(json_last_error_msg())) {
      throw new Exception('Identification invalide');
    }
    
    if (empty($user['firstname']) || empty($user['lastname']) || empty($user['email']) || empty($user['id'])) {
      throw new Exception('Identification invalide');
    } 
  }
  catch(Exception $e) {
    exit(json_encode([
      'error' => true,
      'authent' => true,
      'message' => $e->getMessage()
    ]));
  }
  
  try {
    $db = getDB();
    $request = $db->query('SELECT firstname, lastname, email, arrival_date, departure_date FROM `presences` INNER JOIN `users` ON `presences`.user_id = `users`.id');
    $arr = $request->fetchAll(PDO::FETCH_ASSOC);
    $arr = array_map(function($a) {
      if (!empty($a['departure_date'])) {
        $a['departure_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $a['departure_date']);
      }
      $a['arrival_date'] = DateTime::createFromFormat('Y-m-d H:i:s', $a['arrival_date']);
      
      return $a;
    }, $arr);
    
    $today = (new DateTime())->format('Y-m-d');
    
    $arr = array_reduce($arr, function ($rediucer, $curr) use($today) {
      if ($curr['arrival_date']->format('Y-m-d') === $today) {
        $rediucer[] = $curr;
      }
      return $rediucer;
    }, []);
    
    $arr = array_map(function($a) {
      if (!empty($a['departure_date'])) {
        $a['departure_date'] = $a['departure_date']->format('Y-m-d H:i:s');
      }
      $a['arrival_date'] = $a['arrival_date']->format('Y-m-d H:i:s');
      
      return $a;
    }, $arr);
    
    echo json_encode($arr);
  }
  catch (Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, 'get');

Route::add('/api/presence', function() {
  $body = getBody();
  try {
    switch ($body['type']) {
      case 'arrival':
        $user_id = $body['user_id'];
        $db= getDB();
        $request = $db->prepare('INSERT INTO `presences` (`user_id`, `arrival_date`) VALUES(:user_id, :arrival_date)', [
    			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
    		]);
    		
    		$request->execute([
    		  'user_id' => $user_id,
    		  'arrival_date' => (new DateTime())->format('Y-m-d H:i:s')
    		]);
    		
    		echo json_encode([
    		  'error' => false  
    		]);
        break;
        
      case 'departure':
        $user_id = $body['user_id'];
        $db = getDB();
        $request = $db->prepare('SELECT id FROM `presences` WHERE user_id=:user_id ORDER BY id DESC LIMIT 1', [
    			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
    		]);
    		$request->execute([
    		  'user_id' => $user_id
    		]);
    		$arr = $request->fetchAll(PDO::FETCH_ASSOC);
    		if (empty($arr)) {
    		  throw new Exception('Vous devez d\'abord définir une heure d\'arrivée');
    		}
    		$id = $arr[0]['id'];
    		$request = $db->prepare('UPDATE `presences` SET `departure_date`=:departure_date WHERE id=:id', [
    			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
    		]);
    		$request->execute([
    		  'departure_date' => (new DateTime())->format('Y-m-d H:i:s'),
    		  'id' => $id
    		]);
    		
    		echo json_encode([
    		  'error' => false  
    		]);
        break;
        
      default:
        throw new Exception('invalide presence type');
        break;
    }
  }
  catch (Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, 'post');