<?php

require_once("includes/common.php");

// Generate the json
require_once("generate_plex.php");

// Get the html report
$html = file_get_contents(PLEX_REPORT_URL);

// get emails
$emails = $Core->getPlexEmails();

// send email seperately
$Mailgun = new Mailgun;

foreach($emails as $email){
	$result = $Mailgun->send(array(
	    'from'    => EMAIL_FROM,
	    'to'      => array($email),
	    'cc'      => array(),
	    'bcc'      => array(),
	    'subject' => EMAIL_SUBJECT,
	    'html'    => $html,
	));	
}

echo 'complete';