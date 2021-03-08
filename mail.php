<?php

try {
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
    $mail->SetFrom($mail->Username, EMAIL_NAME);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AddAddress($to);
    $mail->CharSet = 'UTF-8';

    foreach ($attachments as $attachment) {
        $mail->addAttachment($attachment); 
    }

    if($mail->Send()) {
        var_dump('success');
    } else {
        var_dump($mail->ErrorInfo);
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
}