<?php

require_once("includes/common.php");


$html=file_get_contents(HTTP_HTTPS.$_SERVER['SERVER_NAME']."/plex/");

//send email
$mailgun = new Mailgun;

$emails = getPlexEmails();

//send seperately
foreach($emails as $email){
	$result = $mailgun->send(array(
	    'from'    => EMAIL_FROM,
	    'to'      => array($email),
	    'cc'      => array(),
	    'bcc'      => array(),
	    'subject' => EMAIL_SUBJECT,
	    'html'    => $html,
	));	
}

echo 'complete';