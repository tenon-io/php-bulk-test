$(document).ready(function () {

    var resTable = $('#results');
    if ((resTable.length === 0) || (typeof resTable === 'undefined')) {
        $('<table/>', {
            'id': 'results',
            'class': 'table table-striped table-bordered'
        }).appendTo('#main');

        $('<caption/>').appendTo('#results');
        $('<thead/>').appendTo('#results');
        $('<tbody/>').appendTo('#results');

        var thRow = $('<tr/>');

        $(thRow).append('<th scope="col">responseID</th>');
        $(thRow).append('<th scope="col">Date Added</th>');
        $(thRow).append('<th scope="col">URL Tested</th>');
        $(thRow).append('<th scope="col">Status</th>');
        $(thRow).append('<th scope="col">Errors</th>');
        $(thRow).append('<th scope="col">Warnings</th>');

        thRow.appendTo('#results thead');

    }

    $('#results caption').liveRegion();

    //@TODO read the existing log.csv file and load the results, if any

    function queueTest() {
        $.get('test.php', function (response) {

            console.log(response);

            if (typeof response !== 'undefined') {
                var logs = $('#results tbody');
                var newRow = $('<tr></tr>');

                if ($('th[id="' + response.responseID + '"]').length == 0) {

                    //@TODO append a link to open the CSV file for the specific page's results to the responseID column
                    $(newRow).append('<th scope="row" id="' + response.responseID + '">' + response.responseID + '</th>');
                    $(newRow).append('<td>' + response.dateAdded + '</td>');
                    $(newRow).append('<td>' + response.url + '</td>');
                    $(newRow).append('<td>' + response.status + '</td>');
                    $(newRow).append('<td>' + response.errors + '</td>');
                    $(newRow).append('<td>' + response.warnings + '</td>');

                    $(logs).prepend(newRow);

                    //Update the table caption
                    $('#results caption').liveRegion({
                        replace: 'true',
                        text: 'Total Tested: ' + response.totalTested + ', Total Remaining: ' + response.totalRemaining
                    });
                }
            }

        }, 'json');
    }


    if ($('#results').length > 0) {
        setInterval(queueTest, 500);
    }
});