<?php
ini_set("display_errors", 1);

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/constants.php');
require_once(__DIR__.'/classes/Core.php');
require_once(__DIR__.'/functions.php');

// Class Instances
$Core = new Core;

function print_r2($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function dd($array){
	print_r2($array);
}