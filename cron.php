<?php

require_once("includes/common.php");

// Generate the json
require_once("generate_plex.php");

// Get the html report
$html = file_get_contents(PLEX_REPORT_URL);

// get emails
$emails = $Core->getPlexEmails();

// send email seperately
foreach($emails as $email){
	$result = $Mailgun->messages()->send(
		MAILGUN_DOMAIN,
		array(
		    'from'    => EMAIL_FROM,
		    'to'      => $email,
		    'subject' => EMAIL_SUBJECT,
		    'html'    => $html,
		)
	);	
}

echo 'complete';