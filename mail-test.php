<?php

	require "vendor/autoload.php";
	
	use garethp\ews\MailAPI;
	use garethp\ews\API\Type\MessageType;
	use garethp\ews\API\Type\BodyType;
	
//	try {
		$ews = MailAPI::withUsernameAndPassword('smtp.office365.com:587', 'nchoquet@norsys.fr', '12041998Yann?');
		$message = new MessageType();
		$message->setBody(new BodyType('Je suis un text <b>en gras</b>'));
		$message->setSubject('test de sujet');
		$message->setToRecipients('nchoquet@norsys.fr');
		$ews->sendMail($message);
//	}
//	catch (Exception $e) {
//		exit($e->getMessage());
//	}
