<?php

require_once("includes/common.php");

// Generate the json
require_once("generate_plex.php");

// Get the html report
$html = file_get_contents(PLEX_REPORT_URL);

// get emails
$emails = $Core->getPlexEmails();
$subject = EMAIL_SUBJECT;

foreach($emails as $email){
	$Core->sendEmail($subject, $html, $email);
}

echo 'complete';