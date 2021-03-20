<?php

// Load ENV
$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/../.env');

// Globals
date_default_timezone_set($_ENV['TIMEZONE']);
define('APP_URL', $_ENV['APP_URL']);

// Email
define("SES_ACCESS_KEY", $_ENV['SES_ACCESS_KEY']);
define("SES_SECRET_KEY", $_ENV['SES_SECRET_KEY']);
define("SES_REGION_ENDPOINT", $_ENV['SES_REGION_ENDPOINT']);
define("EMAIL_FROM", $_ENV['EMAIL_FROM']);
define("EMAIL_SUBJECT", $_ENV['EMAIL_SUBJECT'].' ('.date("Y-m-d").')');
define("REPORT_TITLE", $_ENV['REPORT_TITLE']);
define("REPORT_SUBTITLE", $_ENV['REPORT_SUBTITLE'].' ('.date("Y-m-d").')');

// PLEX
define("PLEX_API_KEY", $_ENV['PLEX_API_KEY']);
define("PLEX_URL", $_ENV['PLEX_URL']);
define("PLEX_REPORT_URL", $_ENV['PLEX_REPORT_URL']);
define("PLEX_SECTIONS", $_ENV['PLEX_SECTIONS']);

// META DATABASE KEYS
define("MOVIEDB_KEY", $_ENV['MOVIEDB_KEY']);
define("TVDB_KEY", $_ENV['TVDB_KEY']);
define("OMDB_KEY", $_ENV['OMDB_KEY']);

// PLEX CLI OPTIONS
$options=array(
	'token'=>PLEX_API_KEY,
	'plex-url'=>PLEX_URL,
	'sections'=>PLEX_SECTIONS,
);