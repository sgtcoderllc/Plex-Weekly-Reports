<?php
ini_set("display_errors", 1);

require_once(dirname(__FILE__).'/configs.php');
require_once(dirname(__FILE__).'/classes/Mailgun.php');
require_once(dirname(__FILE__).'/classes/Core.php');

$Core = new Core;
$Mailgun = new Mailgun;



function print_r2($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}