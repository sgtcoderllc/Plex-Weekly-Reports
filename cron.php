<?php
// * * * * * /usr/local/bin/php $HOME/public_html/cron.php >$HOME/plex_cron.log 2>&1

require_once("includes/common.php");

// CRON LOG PATH
define('LOG_PATH', ABSPATH.'../cron_logs');
if(!file_exists(LOG_PATH)){
	mkdir(LOG_PATH);
}

// CRON LOCK PATH
define('LOCK_PATH', ABSPATH.'../cron_locks');
if(!file_exists(LOCK_PATH)){
	mkdir(LOCK_PATH);
}

use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler([
    'tempDir' => LOCK_PATH
]);


// CRON JOB For Email Stats
$scheduler
	->call(function () {
		exec_plex();
		email_plex();

	    return "Complete";
	})
	->output(LOG_PATH.'/'.LOG_PREFIX.'plex_stats.log')
	->at('0 8 * * 5');

// Let the scheduler execute jobs which are due.
$scheduler->run();

//exec_plex();
//email_plex();