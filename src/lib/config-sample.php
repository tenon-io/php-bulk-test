<?php
/**
 * This is the config file for you to customize this system for your own needs
 */

// debug mode. change this to 'true' to see a bunch of debugging messages for the CURL request. Otherwise leave this to false
define('DEBUG', false);

// You must have an API key to use this. Get one at https://tenon.io/register.php
define('TENON_API_KEY', '');

// You must use the URL for a Tenon API end point. For enterprise users, this might be custom
// but for everyone else, it will be https://tenon.io/api/
define('TENON_API_URL', 'https://tenon.io/api/');

// This is the file path for the queue file.
define('QUEUE_FILE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/queue.txt');

// Max amount of time to wait for each test run.Tenon should never take any longer than 30 seconds.
// in most cases responses are less than 6 seconds, but that often depends on tested page size, # of errors, etc.
define('TEST_TIME_LIMIT', 30);

// Total number of times to retry a URL before giving up.  Useful in case the Tenon API returns 500 or something
define('RETRY_LIMIT', 3);

// These are your Tenon request options. For more info, see:
// https://tenon.io/documentation/understanding-request-parameters.php
$tenonOpts = array(
    'level'          => 'AAA', // minimum WCAG Level
    'certainty'      => 0, // minimum test certainty
    'priority'       => 0, // minimum priority
    'systemID'       => '', // system ID - left blank by default
    'reportID'       => '', // report ID - left blank by default
    'viewPortHeight' => '', //height of the viewport for Tenon to test in
    'viewPortWidth'  => '', //width of the viewport for Tenon to test in
    'uaString'       => '', // user agent string for Tenon to use
    'ref'            => 0, // whether to include reference material in the response
    'store'          => 0 // whether to store the response data on Tenon's servers
);

// Database connection details
$dbConnection['dbName'] = '';
$dbConnection['user'] = '';
$dbConnection['pass'] = '';
$dbConnection['opts'] = null;
$dbConnection['dbType'] = 'mysql';
$dbConnection['hostORpath'] =  $_SERVER['MYSQL_HOST'];