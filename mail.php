<?php

require "vendor/autoload.php";

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\SMTP;
use \PHPMailer\PHPMailer\POP3;

try {
    $pop = new POP3(); /*Create a new object for pop3*/
    $pop->authorise('outlook.office365.com', 995, 30, 'nchoquet@norsys.fr', '12041998Yann?', 1); /*login in to pop3 */

    $mail = new PHPMailer();  // Cree un nouvel objet PHPMailer
    $mail->IsSMTP(); // active SMTP
    $mail->isHTML(true);
    $mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true)
       ); /*Skip SSL Errors(if any),generally not needed*/
    //$mail->SMTPDebug = SMTP::DEBUG_OFF;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
    //$mail->SMTPAuth = true;  // Authentification SMTP active
    //$mail->SMTPSecure = false; // Gmail REQUIERT Le transfert securise
    $mail->SMTPSecure = 'tls';

    $mail->Host = 'smtp.office365.com';
    //$mail->Port = 587;
    //$mail->Username = 'nchoquet@norsys.com';
    //$mail->Password = '12041998Yann?';
    $mail->SetFrom('nchoquet@norsys.fr', 'Nicolas CHOQUET');
    $mail->Subject = 'subject';
    $mail->Body = 'body';
    $mail->AddAddress('nchoquet@norsys.fr');
    $mail->CharSet = 'UTF-8';

    if($mail->Send()) {
        var_dump('success');
    } else {
        var_dump($mail->ErrorInfo);
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
}