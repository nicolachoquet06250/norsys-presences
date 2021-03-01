<?php
use Steampixel\Route;
use Spipu\Html2Pdf\Html2Pdf;

Route::add('/api/export/([0-9]{0,4}\-[0-9]{0,2}\-[0-9]{0,2})', function ($date) {
  $body = getBody();
  //$body = $_GET;
  $email = $body['email'];
  
  $date_timestamp = strtotime($date);
  $days = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
  $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aoùt', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
  
  $date_str = $days[intval(date('N', $date_timestamp))].' '.date('d', $date_timestamp).' '.$months[intval(date('m', $date_timestamp))].' '.date('Y', $date_timestamp);
  
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
    
    $arr = array_reduce($arr, function ($rediucer, $curr) use($date) {
      if ($curr['arrival_date']->format('Y-m-d') === $date) {
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
  }
  catch (Exception $e) {
    exit(json_encode([
      'error' => true,
      'message' => $e->getMessage()
    ]));
  }
  
  $html = '
<!DOCTYPE html>
<html lang="en">
<head>
		  <meta name="viewport" content="width=device-width, initial-scale=1">
			<title>Norsys Sophia | Fiche de présence</title>
			<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
			<style>
			  '.file_get_contents(__DIR__.'/../assets/styles/bootstrap.css').'
			</style>
</head>
<body>
  <div class="navbar navbar-light bg-light" style="background-color: lightgray;">
    <div class="container-fluid">
      <a class="navbar-brand" href="https://norsys-sophia-presence.nicolaschoquet.fr/" style="color: white;">
        <img src="https://avatars.githubusercontent.com/u/2487851?s=280&v=4" alt="Logo norsys" width="30" height="30" class="d-inline-block align-top" style="margin-right: 5px; margin-left: 5px;" />
        Fiche de présence du <strong id="today">'.$date_str.'</strong>
      </a>
    </div>
  </div>
  <table class="table table-responsive">
	  <thead>
	    <tr style="border-bottom: 1px solid white">
	      <th>Date</th>
	      <th>Prénom Nom</th>
	      <th>Heure d\'arrivée</th>
	      <th>Heure de départ</th>
	    </tr>
	  </thead>
	  <tbody>
	  '.(function() use($date, $arr) {
	    $date_timestamp = strtotime($date);
	    
	    $tbody = '';
	    foreach ($arr as $line) {
  	    $arrival_timestamp = strtotime($line['arrival_date']);
  	    $departure_timestamp = strtotime($line['departure_date']);
  	    if (empty($line['departure_date'])) {
  	      $departure_timestamp = null;
  	    }
  	    
	      $tbody .= '<tr>
	        <td>' . date('d', $date_timestamp) . '/' . date('m', $date_timestamp) . '/' . date('Y', $date_timestamp) . '</td>
	        <td>' . $line['lastname'] . ' ' . substr($line['firstname'], 0, 1) . '.</td>
	        <td>' . (date('H:i', $arrival_timestamp)) . '</td>
	        <td>' . (empty($departure_timestamp) ? '' : date('H:i', $departure_timestamp)) . '</td>
	      </tr>';
	    }
	    
	    if (empty($tbody)) {
	      $tbody = '
    	    <tr>
    	      <td colspan="4" style="text-align: center;"><strong>Personne à l\'agence aujourd\'hui</strong></td>
    	    </tr>
  	    ';
	    }
	    
	    return $tbody;
	  })().'
	  </tbody>
	</table>
</body>
</html>';

  $html2pdf = new Html2Pdf();
  $html2pdf->writeHTML($html);
  $html2pdf->output(__DIR__.'/../assets/pdfs/export-norsys-fiche-presence-'.$date.'.pdf', 'F');
  
  $result = [
    'pdf_path' => '/assets/pdfs/export-norsys-fiche-presence-'.$date.'.pdf'
  ];
  
  $mail = smtpMailer($email, $html, 'Export feuille de présence Norsys '.$date, __DIR__.'/../'.$result['pdf_path']);
  
  if ($mail === true) {
    echo json_encode($result);
    unlink(__DIR__.'/../'.$result['pdf_path']);
  } else {
    echo json_encode([
      'error' => true,
      'message' => $mail
    ]);
  }
}, 'post');