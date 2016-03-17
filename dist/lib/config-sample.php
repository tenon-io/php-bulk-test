<?php
/**
 * This is the config file for you to customize this system for your own needs
 */

// error reporting. You should actually be setting this at your server
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);

// debug mode. change this to 'true' to see a bunch of debugging messages for the CURL request. Otherwise leave this to false
define('DEBUG', false);

// You must have an API key to use this. Get one at https://tenon.io/register.php
define('TENON_API_KEY', '');

// You must use the URL for a Tenon API end point. For enterprise users, this might be custom
// but for everyone else, it will be https://tenon.io/api/index.php
define('TENON_API_URL', 'https://tenon.io/api/index.php');

// This is the file path for the queue file.
define('QUEUE_FILE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/queue.txt');

// This is the folder path for the results export.
define('EXPORT_FOLDER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/results/');

// name for the "all issues" file
define('EXPORT_FILE_NAME', 'tenon.csv');

// Max amount of time to wait for each test run.Tenon should never take any longer than 30 seconds.
// in most cases responses are less than 6 seconds, but that often depends on tested page size, # of errors, etc.
define('TEST_TIME_LIMIT', 30);

// Total number of times to retry a URL before giving up.  Useful in case the Tenon API returns 500 or something.
// Don't set this too high. Any more than 5 and you're probably wasting your time.
define('RETRY_LIMIT', 3);

// These are your Tenon request options. For more info, see:
// https://tenon.io/documentation/understanding-request-parameters.php
$tenonOpts = array(
    'level'          => 'AAA', // minimum WCAG Level
    'certainty'      => 0, // minimum test certainty
    'priority'       => 0, // minimum priority
    'projectID'      => '', // project ID - left blank by default
    'viewPortHeight' => '', //height of the viewport for Tenon to test in
    'viewPortWidth'  => '', //width of the viewport for Tenon to test in
    'uaString'       => '', // user agent string for Tenon to use during testing.
    'ref'            => 0, // whether to include reference material in the response -  set to '1' if you want the reference link
    'store'          => 0 // whether to store the response data on Tenon's servers - set to '1' if you want the results stored on Tenon
);

// Database connection details
$dbConnection['dbName'] = '';
$dbConnection['user'] = '';
$dbConnection['pass'] = '';
$dbConnection['opts'] = null;
$dbConnection['dbType'] = 'mysql';
$dbConnection['hostORpath'] = 'localhost';


/**
 * autoloader (lazy loading) function
 *
 * @param  string $class_name
 *
 * @return void
 */
function autoloader($class_name)
{
    $class_directories = array(
        $_SERVER['DOCUMENT_ROOT'] . '/lib/',
        $_SERVER['DOCUMENT_ROOT'] . '/lib/gs_libs/');

    //for each directory
    foreach ($class_directories as $directory) {
        //see if the file exists
        if (file_exists($directory . $class_name . '.class.php')) {
            require_once($directory . $class_name . '.class.php');
            //only require the class once, so quit after to save effort
            // (if you got more than one, then name them something else)
            return;
        }
    }
}

spl_autoload_register('autoloader');
