<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8"/>
    <title>Reports: Tenon Queue Tester</title>
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="bower_components/bootstrap/dist/css/bootstrap.min.css"/>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="bower_components/fontawesome/css/font-awesome.min.css">
    <script src="bower_components/jquery-live-regions/dist/liveRegion.min.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<header role="banner"><a href="https://tenon.io"><img src="tenon-logo-no-border-dark.png" alt="Tenon.io"></a>
</header>
<main id="main" role="main">
    <h1>Reports</h1>

    <section id="reports">
        <h2>Summary</h2>
        <table class="table">
            <tr>
                <td>Average errors per page</td>
                <td id="countAvgErrorsPerPage"></td>
            </tr>
            <tr>
                <td>Average warnings per page</td>
                <td id="countAvgWarningsPerPage"></td>
            </tr>
            <tr>
                <td>Average issues per page</td>
                <td id="countAvgIssuesPerPage"></td>
            </tr>
            <tr>
                <td>Average issue certainty</td>
                <td id="getAvgCertainty"></td>
            </tr>
            <tr>
                <td>Average issue priority</td>
                <td id="getAvgPriority"></td>
            </tr>
        </table>

        <h2>Issues by Distinct Test ID</h2>

        <div id="countIssuesByDistinctTID"></div>

        <h2>Duplicated Issues</h2>

        <div id="countIssuesByDistinctSignatures"></div>

        <h2>Issues by WCAG Level</h2>

        <div id="countIssuesByWCAGLevel"></div>

        <h2>Issues by WCAG SC</h2>

        <div id="countIssuesByWCAGSC"></div>

        <h2>Issues by Certainty</h2>

        <div id="countIssuesByCertainty"></div>

        <h2>Issues by Priority</h2>

        <div id="countIssuesByPriority"></div>
    </section>
</main>
<script src="reports.js"></script>

</body>
</html>