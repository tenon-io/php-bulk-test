<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);

if($_GET['clean'] == 'true'){
    $truncate = true;
}
else{
    $truncate = false;
}

$tenon->populateQueue($truncate);