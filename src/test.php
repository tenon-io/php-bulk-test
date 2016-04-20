<?php
// keep going even if user bounces off the page so that at very least *this* test runs successfully.
ignore_user_abort(true);

require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

set_time_limit(TEST_TIME_LIMIT);

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);

// check to see if there are any pages in the queue
// we do this by counting the length of the queue. Proceed if the queue is > 1
if ($tenon->getQueueLength() < 1) {
    exit;
}
else{
    $next = $tenon->pluckURL();

    if (false !== $next) {

        $tenon->opts['url'] = $next['url'];


        $tenon->submit(DEBUG);

        //return tenon's response code
        http_response_code($tenon->tCode);

        // Sanity check to make sure everything went OK
        $tenon->decodeResponse();

        //assemble a response summary for appending to the #results table
        $response = array(
            'responseID' => $tenon->rspArray['request']['responseID'],
            'dateAdded'  => $tenon->rspArray['responseTime'],
            'url'        => Strings::truncateToMiddle($tenon->rspArray['request']['url'], 75),
            'status'     => $tenon->rspArray['status'],
            'errors'     => $tenon->rspArray['resultSummary']['issues']['totalErrors'],
            'warnings'   => $tenon->rspArray['resultSummary']['issues']['totalWarnings'],
        );

        // This logs the response no matter what
        $tenon->logResponse($response);

        // this section updates the queued record
        if ($tenon->rspArray['status'] === 200) {
            $update = $response;
            unset($update['dateAdded']);
            unset($update['url']);
            $update['tested'] = '1';
            $update['rawResponse'] = $tenon->tenonResponse;
            $update['dateTested'] = Date::UTC2MySQLDateTime();

            $tenon->logIssues();
        } else {

            $failed = true;

            $update['status'] = $tenon->rspArray['status'];
            $update['tested'] = '0';
            $update['retries'] = $next['retries'] + 1;
        }
        $update['testing'] = '0';

        // if this test run failed, it might be due to a bad URL
        // check the MIME type to be sure
        if($failed === true){

            // get the MIME type from the headers
            $mimeInfo = Network::getRemoteMimeType($tenon->opts['url']);

            // sometimes the MIME type isn't properly set by some servers.
            // returning FALSE from getRemoteMimeType isn't a guarantee
            // that the MIME type is bad.
            if(false !== $mimeInfo){
                // MIME type header is actually the MIME type and charset.
                // all we want is the content type so split it up
                $parts = explode(';', $mimeInfo);

                // if the URL isn't HTML then it must've gotten here by accident, so
                // delete it instead of continuing to try.
                if(false === Strings::contains('html', $parts[0])){
                    $tenon->deleteByURL($tenon->opts['url']);
                }
            }
        }
        else{
            $tenon->updateQueuedURL($update, $next['queueID']);
        }
    }
}

$response['totalRemaining'] = $tenon->getTotalUntested();
$response['totalTested'] = $tenon->getTotalTested();
$response['totalFailed'] = $tenon->getTotalFailed();


echo json_encode($response);
$tenon = null;
$response = null;
