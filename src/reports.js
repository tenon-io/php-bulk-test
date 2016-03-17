$(document).ready(function () {

    $.get("lib/countIssuesByDistinctTID.php", function (data) {
        $('#countIssuesByDistinctTID').html(data).getTablePercentages();
    });
    $.get("lib/countAvgErrorsPerPage.php", function (data) {
        $('#countAvgErrorsPerPage').html(data).getTablePercentages();
    });
    $.get("lib/countAvgWarningsPerPage.php", function (data) {
        $('#countAvgWarningsPerPage').html(data).getTablePercentages();
    });
    $.get("lib/countAvgIssuesPerPage.php", function (data) {
        $('#countAvgIssuesPerPage').html(data).getTablePercentages();
    });
    $.get("lib/countIssuesByDistinctSignatures.php", function (data) {
        $('#countIssuesByDistinctSignatures').html(data).getTablePercentages();
    });
    $.get("lib/countIssuesByWCAGLevel.php", function (data) {
        $('#countIssuesByWCAGLevel').html(data).getTablePercentages();
    });
    $.get("lib/countIssuesByWCAGSC.php", function (data) {
        $('#countIssuesByWCAGSC').html(data).getTablePercentages();
    });
    $.get("lib/getAvgCertainty.php", function (data) {
        $('#getAvgCertainty').html(data).getTablePercentages();
    });
    $.get("lib/countIssuesByCertainty.php", function (data) {
        $('#countIssuesByCertainty').html(data).getTablePercentages();
    });
    $.get("lib/getAvgPriority.php", function (data) {
        $('#getAvgPriority').html(data).getTablePercentages();
    });
    $.get("lib/countIssuesByPriority.php", function (data) {
        $('#countIssuesByPriority').html(data).getTablePercentages();
    });

});