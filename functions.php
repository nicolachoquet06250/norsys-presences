<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getDB() {
	if (!defined('DB_ENGINE') || !defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
		throw new Exception('Vous devez définir des crédentials pour la base de donnée');
	}
	return new PDO(DB_ENGINE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
}

function getBody() {
  $body = file_get_contents('php://input');
	$body = json_decode($body, true);
	return $body;
}

function smtpMailer($to, $body, $subject, ...$attachments) {
	$mail = new PHPMailer();  // Cree un nouvel objet PHPMailer
	$mail->IsSMTP(); // active SMTP
	$mail->isHTML(true);
	$mail->SMTPDebug = SMTP::DEBUG_OFF;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
	$mail->SMTPAuth = true;  // Authentification SMTP active
	$mail->SMTPSecure = false; // Gmail REQUIERT Le transfert securise
	if (defined(EMAIL_ENCRIPTION) && EMAIL_ENCRIPTION) {
		$mail->SMTPSecure = EMAIL_ENCRIPTION;
	}
	$mail->Host = EMAIL_HOST;
	$mail->Port = EMAIL_PORT;
	$mail->Username = EMAIL;
	$mail->Password = EMAIL_PASSWORD;
	var_dump($mail->Username, EMAIL_NAME);
	$mail->SetFrom($mail->Username, EMAIL_NAME);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddAddress($to);
	$mail->CharSet = 'UTF-8';
	
	foreach ($attachments as $attachment) {
    	$mail->addAttachment($attachment); 
	}
	
	if(!$mail->Send()) {
		return 'Mail error: '.$mail->ErrorInfo;
	} else {
		return true;
	}
}

function nbDaysInMonth($month , $year) {
  $inMonth = $year * 12 + $month; 
  if (($inMonth > 2037 * 12 - 1) || ($inMonth < 1970)) return 0;
  $next_year = floor(($inMonth + 1) / 12); 
  $next_month = $inMonth + 1 - 12 * $next_year; 
  $timing = mktime(0, 0, 1, $next_month, 1, $next_year) - mktime(0, 0, 1, $month, 1, $year);
  return round(($timing / (3600 * 24))); 
}

function isBisextilYear($year = null) {
  if (is_null($year)) $year = date('Y');
  return nbDaysInMonth('02', $year) === 29;
}

function get_week_extremity_days($week_number, $year){
  return [
      'first_day' => date("Y-m-d", strtotime('First Monday January ' . $year . ' +' . ($week_number - 1) . ' Week')),
      'last_day' => date("Y-m-d", strtotime('First Monday January ' . $year . ' +' . $week_number . ' Week -3 day'))
  ];
}

function init_config() {
	$config = file_get_contents(__DIR__.'/config.json');
	$config = json_decode($config, true);
	
	foreach ($config as $key => $value) {
		if (!defined($key)) {
			define($key, $value);
		}
	}
}
