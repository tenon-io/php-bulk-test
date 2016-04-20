'use strict';
$(document).ready(function () {

    $.get('lib/countIssuesByDistinctTID.php', function (data) {
        $('#countIssuesByDistinctTID').html(data);
        $('#countIssuesByDistinctTID').getTablePercentages();
    });

    $.get('lib/totalPagesTested.php', function (data) {
        $('#totalPagesTested').html(data);
    });

    $.get('lib/countAvgErrorsPerPage.php', function (data) {
        $('#countAvgErrorsPerPage').html(data);
        $('#countAvgErrorsPerPage').getTablePercentages();
    });

    $.get('lib/countAvgWarningsPerPage.php', function (data) {
        $('#countAvgWarningsPerPage').html(data);
        $('#countAvgWarningsPerPage').getTablePercentages();
    });

    $.get('lib/countAvgIssuesPerPage.php', function (data) {
        $('#countAvgIssuesPerPage').html(data);
        $('#countAvgIssuesPerPage').getTablePercentages();
    });

    $.get('lib/countIssuesByDistinctSignatures.php', function (data) {
        $('#countIssuesByDistinctSignatures').html(data);
        $('#countIssuesByDistinctSignatures').getTablePercentages();
    });

    $.get('lib/countIssuesByWCAGLevel.php', function (data) {
        $('#countIssuesByWCAGLevel').html(data);
        $('#countIssuesByWCAGLevel').getTablePercentages();
    });

    $.get('lib/countIssuesByWCAGSC.php', function (data) {
        $('#countIssuesByWCAGSC').html(data);
        $('#countIssuesByWCAGSC').getTablePercentages();
    });

    $.get('lib/getAvgCertainty.php', function (data) {
        $('#getAvgCertainty').html(data);
        $('#getAvgCertainty').getTablePercentages();
    });

    $.get('lib/countIssuesByCertainty.php', function (data) {
        $('#countIssuesByCertainty').html(data);
        $('#countIssuesByCertainty').getTablePercentages();
    });

    $.get('lib/getAvgPriority.php', function (data) {
        $('#getAvgPriority').html(data);
        $('#getAvgPriority').getTablePercentages();
    });

    $.get('lib/countIssuesByPriority.php', function (data) {
        $('#countIssuesByPriority').html(data);
        $('#countIssuesByPriority').getTablePercentages();
    });
});
