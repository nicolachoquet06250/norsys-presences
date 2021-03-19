<?php
use Steampixel\Route;

Route::add('/api/calendar/([0-9]{0,4})/([0-9]{0,2})', function ($year, $month) {
  $days = nbDaysInMonth($month, $year);
  $previous_month = $month;
  $previous_month_year = $year;
  if (intval($previous_month) === 1) {
    $previous_month = 12;
    $previous_month_year = intval($previous_month_year) - 1;
  }
  $previous_month_days = nbDaysInMonth(intval($previous_month) - 1, $previous_month_year);
  $bisextil = isBisextilYear($year);
  
  $calendar = [];
  
  for ($i = 0; $i < $days; $i++) {
    $day = intval(date('N', strtotime($year.'-'.$month.'-'.($i + 1))));
    
    if (!isset($calendar[date('W', strtotime($year.'-'.$month.'-'.($i + 1)))])) {
       $calendar[date('W', strtotime($year.'-'.$month.'-'.($i + 1)))] = [];
    }
    $reservations = [];
    $presences = [];
    
    try {
      $db = getDB();
      $content = '';
      if (is_file(__DIR__.'/test.txt')) {
        $content = file_get_contents(__DIR__.'/test.txt');
      }
      $request = $db->prepare('SELECT `reservations`.`id_user`, 
                                      `reservations`.`id` id_reservation, 
                                      `reservations`.`date` date, 
                                      `email`, `firstname`, `lastname` 
                              FROM `reservations` INNER JOIN `users` 
                                ON `reservations`.`id_user` = `users`.`id` 
                                  WHERE DATE_FORMAT(date, \'%Y-%m-%d\') = :date', 
                                  [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
  		$request->execute([
  		  'date' => $year.'-'.($month < 10 ? '0' : '').$month.'-'.($i + 1 < 10 ? '0' : '').($i + 1)
  		]);
  		$reservations = $request->fetchAll(PDO::FETCH_ASSOC);
      
  		$request = $db->prepare('SELECT `presences`.id, 
  		                                DATE_FORMAT(arrival_date, \'%H:%i\') arrival, 
  		                                DATE_FORMAT(departure_date, \'%H:%i\') departure, 
  		                                `firstname`, `lastname`, `email`, `name` 
  		                          FROM `presences` INNER JOIN `users` INNER JOIN `agencies` 
  		                            ON `presences`.`user_id` = `users`.`id` 
  		                              AND `users`.`agency_id` = `agencies`.`id` 
  		                              WHERE DATE_FORMAT(arrival_date, \'%Y-%m-%d\') = :date', 
                                  [ PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ]);
      $request->execute([
        'date' => $year.'-'.($month < 10 ? '0' : '').$month.'-'.($i < 10 ? '0' : '').($i + 1)
      ]);
  		$presences = $request->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(Exception $e) {
      exit(json_encode([
        'error' => true,
        'message' => $e->getMessage()
      ]));
    }
    
    $calendar[date('W', strtotime($year.'-'.$month.'-'.($i + 1)))][] = [
      'day' => $day,
      'date' => $year.'-'.$month.'-'.($i + 1),
      'reservations' => $reservations,
      'presences' => $presences
    ];
  }
  
  echo json_encode([
    'year' => intval($year),
    'month' => intval($month),
    'bisextil' => $bisextil,
    'nb_days' => [
      'previous' => $previous_month_days,
      'current' => $days
    ],
    'calendar' => $calendar
  ]);
}, 'get');

Route::add('/api/reservation', function () {
  $body = getBody();
  $user_id = $body['user_id'];
  $date = $body['date'];
  try {
    if (empty($user_id) || empty($date)) {
      throw new Exception('Une erreur est survenue lors de l\'enregistrement de votre réservation');
    }
    
    $db = getDB();
    $request = $db->prepare('INSERT INTO `reservations` (`id_user`, `date`) VALUES(:id_user, :date)', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute([
		  'id_user' => $user_id,
		  'date' => $date
		]);
		
		echo json_encode([
		  'error' => false,
		  'user_id' => intval($user_id),
		  'date' => $date
		]);
  }
  catch(Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, 'post');

Route::add('/api/reservation', function() {
  $body = getBody();
  $user_id = $body['user_id'];
  $date = $body['date'];
  
  try {
    if (empty($user_id) || empty($date)) {
      throw new Exception('Une erreur est survenue lors de l\'annulation de votre réservation');
    }
    
    $db = getDB();
    $request = $db->prepare('DELETE FROM `reservations` WHERE `id_user` = :id_user AND DATE_FORMAT(date, \'%Y-%m-%d\') = :date', [
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
		]);
		$request->execute([
		  'id_user' => $user_id,
		  'date' => explode('-', $date)[0].'-'.(intval(explode('-', $date)[1]) < 10 ? '0' : '').explode('-', $date)[1].'-'.(intval(explode('-', $date)[2]) < 10 ? '0' : '').explode('-', $date)[2]
		]);
		
		echo json_encode([
		  'error' => false,
		  'user_id' => intval($user_id),
		  'date' => $date
		]);
  }
  catch(Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, ['delete', 'post']);