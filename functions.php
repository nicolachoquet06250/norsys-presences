<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getDB() {
	return new PDO('mysql:host=mysql-nicolas-choquet-2.alwaysdata.net;dbname=nicolas-choquet-2_norsys_sophia_presences', '156090', '2669NICOLAS2107');
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
	$mail->Host = 'smtp-nicolas-choquet.alwaysdata.net';
	$mail->Port = 25;
	$mail->Username = 'norsys@nicolaschoquet.fr';
	$mail->Password = '2669NICOLAS2107';
	$mail->SetFrom($mail->Username, 'Norsys');
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