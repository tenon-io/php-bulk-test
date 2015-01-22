<?php
// keep going even if user bounces off the page so that at very least *this* test runs successfully.
ignore_user_abort(true);

require($_SERVER['DOCUMENT_ROOT'] . '/lib/config.php');

set_time_limit(TEST_TIME_LIMIT);

$tenon = new tenonQueueTest($tenonOpts, $dbConnection);

// check to see if there are any pages in the queue
// we do this by counting the length of the queue. Proceed if the queue is > 1
if ($tenon->getQueueLength() > 0) {
    $next = $tenon->pluckURL();

    if(false !== $next) {

        $tenon->opts['url'] = $next['url'];

        $tenon->submit(DEBUG);

        //return tenon's response code
        http_response_code($tenon->tCode);

        // Sanity check to make sure everything went OK
        $tenon->decodeResponse();

        //assemble a response summary for appending to the #results table
        $response = array(
                'responseID'     => $tenon->rspArray['request']['responseID'],
                'dateAdded'      => $tenon->rspArray['responseTime'],
                'url'            => $tenon->rspArray['request']['url'],
                'status'         => $tenon->rspArray['status'],
                'errors'         => $tenon->rspArray['resultSummary']['issues']['totalErrors'],
                'warnings'       => $tenon->rspArray['resultSummary']['issues']['totalWarnings'],
        );

        // This logs the response no matter what
        $tenon->logResponse($response);

        // this section updates the queued record
        if($tenon->rspArray['status'] === 200){
            $update = $response;
            unset($update['dateAdded']);
            unset($update['url']);
            $update['tested'] = '1';
            $update['rawResponse'] = $tenon->tenonResponse;
            $update['dateTested'] = Date::UTC2MySQLDateTime();

            $tenon->logIssues();
        }
        else{
            $update['status'] = $tenon->rspArray['status'];
            $update['tested'] = '0';
            $update['retries'] = $next['retries'] + 1;

            sleep(1);
        }

        $update['testing'] = '0';
        $tenon->updateQueuedURL($update, $next['queueID']);
    }

}

$response['totalRemaining'] = $tenon->getTotalUntested();
$response['totalTested'] = $tenon->getTotalTested();
$response['totalFailed'] = $tenon->getTotalFailed();

echo json_encode($response);