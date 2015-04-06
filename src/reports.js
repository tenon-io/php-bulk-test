$(document).ready(function () {

    function doReports() {
        $.get("lib/countIssuesByDistinctTID.php", function (data) {
            $('#countIssuesByDistinctTID').html(data);
        });
        $.get("lib/countAvgErrorsPerPage.php", function (data) {
            $('#countAvgErrorsPerPage').html(data);
        });
        $.get("lib/countAvgWarningsPerPage.php", function (data) {
            $('#countAvgWarningsPerPage').html(data);
        });
        $.get("lib/countAvgIssuesPerPage.php", function (data) {
            $('#countAvgIssuesPerPage').html(data);
        });
        $.get("lib/countIssuesByDistinctSignatures.php", function (data) {
            $('#countIssuesByDistinctSignatures').html(data);
        });
        $.get("lib/countIssuesByWCAGLevel.php", function (data) {
            $('#countIssuesByWCAGLevel').html(data);
        });
        $.get("lib/countIssuesByWCAGSC.php", function (data) {
            $('#countIssuesByWCAGSC').html(data);
        });
        $.get("lib/getAvgCertainty.php", function (data) {
            $('#getAvgCertainty').html(data);
        });
        $.get("lib/countIssuesByCertainty.php", function (data) {
            $('#countIssuesByCertainty').html(data);
        });
        $.get("lib/getAvgPriority.php", function (data) {
            $('#getAvgPriority').html(data);
        });
        $.get("lib/countIssuesByPriority.php", function (data) {
            $('#countIssuesByPriority').html(data);
        });
    }

    doReports();

    if ($('#reports').length > 0) {
        setInterval(doReports, 2000);
    }
});