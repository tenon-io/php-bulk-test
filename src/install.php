<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);


// Step 1, make sure the tenon.sql file exists
$sqlFile = $_SERVER['DOCUMENT_ROOT'] . '/install.sql';
if (file_exists($sqlFile)) {
    echo '<p>The file, <em>' . $sqlFile . '</em> exists. Continuing</p>';
} else {
    echo '<p>The file, <em>' . $sqlFile . '</em> does not exist. Cannot Continue.</p>';
    var_dump($sqlFile);
    exit;
}

// Step 2, wipe out existing tables
// Step 2.1: connect to the database
$db = new dbPDO($dbConnection['dbName'], $dbConnection['user'], $dbConnection['pass'], $dbConnection['opts'], $dbConnection['dbType'], $dbConnection['hostORpath'], $dbConnection['port']);
if ((false === $db) || (get_class($db) !== 'dbPDO')) {
    echo '<p>Database object could not be created. Cannot continue.</p>';
    var_dump($db);
    exit;
} else {
    echo '<p>Database object created. Continuing.</p>';
}

// Step 2.2: get a list of all tables
$tables = $db->ListTables($dbConnection['dbName']);
if ((false === $tables) || (!is_array($tables))) {
    echo '<p>Could not fetch list of tables.  Cannot continue.</p>';
    var_dump($tables);
    exit;
} else {
    echo '<p>Table list created. Continuing.</p>';
}

//Step 2.3: loop through the tables and dump each one
foreach ($tables AS $t) {
    if (false === $db->DropTable($t)) {
        echo '<p>Cannot drop table ' . $t . '</p>';
        var_dump($t);
        exit;
    } else {
        echo '<p>Table ' . $t . ' dropped</p>';
    }
}

// Step 3, upload and execute the SQL file
$sql = file_get_contents($sqlFile);

if (false === $sql) {
    echo '<p>Could not get SQL file contents!</p>';
    var_dump($sql);
    exit;
} else {
    echo '<p>Almost there! SQL File read and ready to go.</p>';
}

if (false === $db->RawExec($sql)) {
    echo '<p>Installation aborted. Could not create the tables.</p>';
    exit;
} else {
    echo '<p>Tables Created. Populating Queue Table.</p>';

    $tenon->install();
}

?>
If you don't see any errors above, <a href="index.php">go run the tests</a>!