<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');
$reports = new reports($dbConnection);


$data = $reports->countIssuesByWCAGLevel();

if (false === $data) {
    echo 'NO DATA';
    exit;
}

$out = '';

$out .= '<table class="table table-striped table-condensed">';
$out .= '<thead>';
$out .= '<tr>';
$out .= '<th>WCAG Level</th>';
$out .= '<th>Count</th>';
$out .= '<th scope="col">Percent</th>';
$out .= '</tr>';
$out .= '</thead>';
$out .= '<tbody>';

foreach ($data AS $k => $v) {
    $out .= '<tr>';
    $out .= '<td>' . $k . '</td>';
    $out .= '<td class="data">' . $v . '</td>';
    $out .= '<td class="pct"></td>';
    $out .= '</tr>';
}

$out .= '</tbody>';
$out .= '</table>';

echo $out;
