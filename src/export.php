<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);

$tenon->exportIssues();