<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');
$reports = new reports($dbConnection);

$data = $reports->countIssuesByDistinctTID();

if (false === $data) {
    echo 'NO DATA';
    exit;
}

$out = '';
$out .= '<table class="table table-striped table-condensed"><caption>' . count($data) . ' Distinct Error Types</caption>';
$out .= '<thead>';
$out .= '<tr>';
$out .= '<th scope="col">Error</th>';
$out .= '<th scope="col">Num. Instances</th>';
$out .= '</tr>';
$out .= '</thead>';
$out .= '<tbody>';

foreach ($data as $k => $v) {
    $out .= '<tr>';
    $out .= '<td>' . $k . '</td>';
    $out .= '<td>' . $v . '</td>';
    $out .= '</tr>';
}

$out .= '</tbody>';
$out .= '</table>';

echo $out;