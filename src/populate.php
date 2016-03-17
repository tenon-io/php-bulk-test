<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);

if ($_GET['clean'] == 'true') {
    $truncate = true;
} else {
    $truncate = false;
}

$tenon->populateQueue($truncate);
?>
<p>Queue Table Populated. If you don't see any errors above, <a href="index.php">go run the tests</a>!</p>