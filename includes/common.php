<?php
ini_set("display_errors", 1);

// Autoload
require_once(__DIR__.'/../vendor/autoload.php');

// Load Constants
require_once(__DIR__.'/constants.php');

// Classes
require_once(dirname(__FILE__).'/classes/Core.php');

// Class Instances
$Core = new Core;

function print_r2($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}