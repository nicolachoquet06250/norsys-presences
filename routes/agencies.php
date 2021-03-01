<?php
use Steampixel\Route;

Route::add('/api/agencies', function () {
  try {
    $db = getDB();
    
    $request = $db->query('SELECT * FROM `agencies`');
    $results = $request->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
  }
  catch (Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
}, 'get');