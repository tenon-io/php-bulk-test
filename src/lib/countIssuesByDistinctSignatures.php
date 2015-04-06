<?php
require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');
$reports = new reports($dbConnection);


$data = $reports->countDuplicateIssuesBySignatures();

if (false === $data) {
    echo 'NO DATA';
    exit;
}

$out = '';

$out .= '<table class="table table-striped table-condensed">
<caption>Identical issues across pages</caption>
<thead>
<tr>
<th scope="col">Error Title</th>
<th scope="col">Num. Instances</th>
</tr>
</thead>
<tbody>';

foreach ($data AS $k => $v) {
    $out .= '<tr>';
    $out .= '<td>' . $k . '</td>';
    $out .= '<td>' . $v . '</td>';
    $out .= '</tr>';
}

$out .= '</tbody></table>';


echo $out;