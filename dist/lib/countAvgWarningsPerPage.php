<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');
$reports = new reports($dbConnection);

$data = $reports->countAvgWarningsPerPage();

if (false === $data) {
    echo 'NO DATA';
    exit;
}

echo round($data);