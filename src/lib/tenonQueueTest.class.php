<?php

class tenonQueueTest extends tenonTest
{

    public $url, $opts, $connection, $tenonResponse, $tURL;

    /**
     * Class constructor
     *
     * @param   array  $opts       options for the request
     * @param   array  $connection database connection details
     */
    public function __construct($opts, $connection)
    {
        $this->url = TENON_API_URL;
        $opts['key'] = TENON_API_KEY;
        $this->opts = $opts;
        $this->tenonResponse = '';
        $this->connection = $connection;
        $this->init();
        $this->now = Date::UTC2MySQLDateTime();

        parent::__construct(TENON_API_URL, $opts);
    }

    /**
     * @return bool
     */
    public function init()
    {
        $this->db = new dbPDO($this->connection['dbName'],
                              $this->connection['user'],
                              $this->connection['pass'],
                              $this->connection['opts'],
                              $this->connection['dbType'],
                              $this->connection['hostORpath'],
                              $this->connection['port']);

        if (false === $this->db) {
            return false;
        }

        return true;
    }

    /**
     * @param $fileName
     *
     * @return mixed
     */
    public function cleanFileName($fileName)
    {
        return preg_replace("/[^A-Za-z\s\s+]/", "-", $fileName);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function logResponse($data)
    {
        $query = sprintf("INSERT INTO responseLog(responseID, dateAdded, url, status, errors, warnings) VALUES('%s', '%s', '%s', '%s','%s', '%s')",
            $this->db->EscapeString($data['responseID']), $this->db->EscapeString($data['dateAdded']), $this->db->EscapeString($data['url']),
            $this->db->EscapeString($data['status']), $this->db->EscapeString($data['errors']), $this->db->EscapeString($data['warnings']));

        return $this->db->InsertData($query);
    }

    /**
     * @return mixed
     */
    public function getQueueLength()
    {
        $query = "SELECT count(*) AS count FROM queue WHERE tested='0' OR retries < '" . RETRY_LIMIT . "' LIMIT 1";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data['count'];
    }

    /**
     * @return mixed
     */
    public function getTotalTested(){
        $query = "SELECT count(*) AS count FROM queue WHERE tested='1' LIMIT 1";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data['count'];
    }

    /**
     * @return mixed
     */
    public function getTotalUntested(){
        $query = "SELECT count(*) AS count FROM queue WHERE tested='0' AND retries < '" . RETRY_LIMIT . "' LIMIT 1";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data['count'];
    }

    /**
     * @return mixed
     */
    public function getTotalFailed(){
        $query = "SELECT count(*) AS count FROM queue WHERE tested='0' AND retries >= '" . RETRY_LIMIT . "' LIMIT 1";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data['count'];
    }

    /**
     * @return bool
     */
    public function queueIsEmpty(){
        if($this->getQueueLength() > 0){
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getLogLength()
    {
        $query = "SELECT count(*) AS count FROM responseLog LIMIT 1";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data['count'];
    }

    /**
     * @return mixed
     */
    public function getTestedLength()
    {
        $query = "SELECT count(*) AS count FROM queue WHERE tested='1' OR retries >= '" . RETRY_LIMIT . "' LIMIT 1";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data['count'];
    }

    /**
     * @return mixed
     */
    public function pluckURL()
    {
        $query = sprintf("SELECT * FROM queue WHERE tested='0' AND testing='0'  AND retries < '%s' ORDER BY rand() LIMIT 1",
                 $this->db->EscapeString(RETRY_LIMIT));

        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if(false === $data){
            return false;
        }

        $q2 = sprintf("UPDATE queue SET testing='1' WHERE queueID='%s'", $this->db->EscapeString($data['queueID']));
        $this->db->UpdateData($q2);

        return $data;
    }

    /**
     * @param $data
     * @param $id
     *
     * @return mixed
     */
    public function updateQueuedURL($data, $id){
        $query = "UPDATE queue SET";

        foreach($data AS $key => $value){
            $updates[] =  Strings::strPad(sprintf("%s='%s'", $this->db->EscapeString($key),$this->db->EscapeString($value)));
        }

        $query .= implode(',', $updates);

        $query .= Strings::strPad(sprintf("WHERE queueID='%s'", $this->db->EscapeString($id)));

        return $this->db->UpdateData($query);

    }

    /**
     * @param  bool  $truncate whether or not to empty the queue table before populating the queue table
     * @return mixed
     */
    public function populateQueue($truncate = true)
    {
        if (!file_exists(QUEUE_FILE_PATH)) {
            echo QUEUE_FILE_PATH . ' does not exist.';
            return false;
        } else {
            $urls = file(QUEUE_FILE_PATH, FILE_SKIP_EMPTY_LINES);

            foreach ($urls AS $url) {
                $queries[] = sprintf("INSERT INTO queue (url, dateAdded) VALUES ('%s', '%s')",
                             $this->db->EscapeString($url), $this->db->EscapeString(Date::UTC2MySQLDateTime()));
            }

            if($truncate === true){
                array_unshift($queries, 'TRUNCATE TABLE queue');
            }

            return $this->db->MultiQuery($queries);
        }
    }

    /**
     * @return mixed
     */
    public function install(){
        $this->populateQueue(true);

        $queries[] = "TRUNCATE TABLE issues";
        $queries[] = "TRUNCATE TABLE responseLog";
        return $this->db->MultiQuery($queries);
    }


    /**
     * @return mixed
     */
    public function logIssues()
    {
        $issues = $this->rspArray['resultSet'];

        $count = count($issues);

        if ($count < 1) {
            return false;
        }

        for ($x = 0; $x < $count; $x++) {
            $queries[] = sprintf("INSERT INTO issues(responseID, url, tID, resultTitle, errorTitle, errorDescription, standards, errorSnippet, issueLine, issueColumn, xpath, certainty, priority, ref, signature) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s',
                    '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $this->db->EscapeString($this->rspArray['request']['responseID']),
                $this->db->EscapeString($this->opts['url']),
                $this->db->EscapeString($issues[$x]['tID']),
                $this->db->EscapeString($issues[$x]['resultTitle']),
                $this->db->EscapeString($issues[$x]['errorTitle']),
                $this->db->EscapeString($issues[$x]['errorDescription']),
                $this->db->EscapeString(implode(', ', $issues[$x]['standards'])),
                $this->db->EscapeString($issues[$x]['errorSnippet']),
                $this->db->EscapeString($issues[$x]['position']['line']),
                $this->db->EscapeString($issues[$x]['position']['column']),
                $this->db->EscapeString($issues[$x]['xpath']),
                $this->db->EscapeString($issues[$x]['certainty']),
                $this->db->EscapeString($issues[$x]['priority']),
                $this->db->EscapeString($issues[$x]['ref']),
                $this->db->EscapeString($issues[$x]['signature']));
        }

        return $this->db->MultiQuery($queries);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getIssuesByResponseID($id){
        $query = sprintf("SELECT * FROM issues WHERE responseID='%s'", $this->db->EscapeString($id));
        return $this->db->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     * @param null $status
     *
     * @return mixed
     */
    public function getDistinctResponses($status=null){
        $query = "SELECT DISTINCT responseID FROM responseLog";

        if(!is_null($status)){
            $query .= Strings::strPad(sprintf("WHERE status='%s'", $this->db->EscapeString($status)));
        }

        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        return $data;

    }

    /**
     * @return bool
     */
    public function exportAllIssues(){
        $query = "SELECT * FROM issues ORDER BY url";

        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        if(false === $data){
            return false;
        }

        return $this->writeResultsToCSV($data, EXPORT_FILE_NAME);
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function exportIssuesByResponseID($id){
        $query = sprintf("SELECT * FROM issues WHERE responseID='%s'", $this->db->EscapeString($id));

        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        if(false === $data){
            return false;
        }

        return $this->writeResultsToCSV($data, $this->cleanFileName($data[0]['url']) . '.csv');

    }

    /**
     * @param bool   $all
     * @param string $status
     *
     * @return bool
     */
    public function exportIssues($all = true, $status = '200'){
        if($all === true) {
            $this->exportAllIssues();
        }
        $responses = $this->getDistinctResponses($status);

        $count = count($responses);

        for($x = 0; $x < $count; $x++){
            $this->exportIssuesByResponseID($responses[$x]['responseID']);
        }

        return true;
    }


    /**
     * @param        $issues
     * @param null   $name
     * @param string $mode
     *
     * @return bool
     */
    public function writeResultsToCSV($issues, $name = null, $mode = 'w+')
    {

        if (is_null($name)) {
            $name = $this->cleanFileName($issues[0]['responseID']) . '.csv';
        }

        $count = count($issues);

        if ($count < 1) {
            return false;
        }

        for ($x = 0; $x < $count; $x++) {

            $rows[$x] = array(
                $issues[$x]['url'],
                $issues[$x]['tID'],
                $issues[$x]['resultTitle'],
                $issues[$x]['errorTitle'],
                $issues[$x]['errorDescription'],
                $issues[$x]['standards'],
                html_entity_decode($issues[$x]['errorSnippet']),
                $issues[$x]['issueLine'],
                $issues[$x]['issueColumn'],
                $issues[$x]['xpath'],
                $issues[$x]['certainty'],
                $issues[$x]['priority'],
                $issues[$x]['ref'],
                $issues[$x]['signature']
            );
        }

        // Put a row of headers up on the beginning
        array_unshift($rows, array('URL', 'testID', 'Best Practice', 'Issue Title', 'Description',
            'WCAG SC', 'Issue Code', 'Line', 'Column', 'xPath', 'Certainty', 'Priority', 'Reference', 'Signature'));

        $fp = fopen(EXPORT_FOLDER_PATH . $name, $mode);

        if (false != $fp) {
            foreach ($rows as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);

            return true;
        }

        return false;
    }
}
