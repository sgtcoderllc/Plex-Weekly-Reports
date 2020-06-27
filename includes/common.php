<?php
ini_set("display_errors", 1);

require dirname(__FILE__).'/../vendor/autoload.php';
use Mailgun\Mailgun;

require_once(dirname(__FILE__).'/configs.php');
require_once(dirname(__FILE__).'/classes/Core.php');

$Core = new Core;
$Mailgun = \Mailgun\Mailgun::create(MAILGUN_KEY);

function print_r2($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}