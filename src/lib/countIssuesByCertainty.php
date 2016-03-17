<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');
$reports = new reports($dbConnection);

$data = $reports->countIssuesByCertainty();

if (false === $data) {
    echo 'NO DATA';
    exit;
}

$out = '';

$out .= '<table class="table table-striped table-condensed">';
$out .= '<thead>';
$out .= '<tr>';
$out .= '<th scope="col">Certainty</th>';
$out .= '<th scope="col">Num. Instances</th>';
$out .= '<th scope="col">Percent</th>';
$out .= '</tr>';
$out .= '</thead>';

$out .= '<tbody>';

foreach ($data AS $k => $v) {
    $out .= '<tr>';
    $out .= '<td>' . $k . '%</td>';
    $out .= '<td class="data">' . $v . '</td>';
    $out .= '<td class="pct"></td>';
    $out .= '</tr>';
}

$out .= '</tbody>';

$out .= '</table>';


echo $out;
