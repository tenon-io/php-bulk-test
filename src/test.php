<?php
// keep going even if user bounces off the page so that at very least *this* test runs successfully.
ignore_user_abort(true);

set_time_limit(TEST_TIME_LIMIT);

require('lib/config.php');
require('lib/tenonTest.class.php');
require('lib/tenonQueueTest.class.php');

$tenon = new tenonQueueTest(TENON_API_URL, $tenonOpts);

// check to see if there are any pages in the queue
// we do this by counting the length of the queue. Proceed if the queue is > 1
if ($tenon->getQueueLength() > 0) {
    if(false !== $tenon->pluckURL()) {

        $tenon->submit(DEBUG);

        //return tenon's response code
        http_response_code($tenon->tCode);

        // Sanity check to make sure everything went OK
        if($tenon->tCode <= MAX_HTTP_CODE) {

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

            $tenon->logResponse($response);
        }
    }

} // if there aren't any remaining items in the queue, say so
else {
    $response = array('totalTested' => $tenon->getLogLength(), 'totalRemaining' => '0');
}

echo json_encode($response);