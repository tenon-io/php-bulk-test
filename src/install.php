<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);

$tenon->install();
?>
If you don't see any errors above, <a href="index.php">go run the tests</a>!