<?php

class tenonQueueTest extends tenonTest
{

    protected $url, $opts;
    public $tenonResponse, $tURL;

    /**
     * Class constructor
     *
     * @param   string $url  the API url to post your request to
     * @param    array $opts options for the request
     */
    public function __construct($url, $opts, $connection)
    {
        $this->url = $url;
        $opts['key'] = TENON_API_KEY;
        $this->opts = $opts;
        $this->tenonResponse = '';
        $this->connection = $connection;
        $this->init();
    }

    public function init()
    {
        $this->db = new dbPDO($this->connection['dbName'], $this->connection['user'], $this->connection['pass'],
            $this->connection['opts'], $this->connection['dbType'], $this->connection['hostORpath'], $this->connection['port']);

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

    public function logResponse($data)
    {
        $query = sprintf("");
        return $this->db->UpdateData($query);
    }


    public function getQueueLength()
    {
        $query = "SELECT count(*) AS count FROM queue WHERE tested='0' OR retries < '" . RETRY_LIMIT . "' LIMIT 1";
        return $this->db->SelectSingleRecord($query);

    }

    public function getLogLength()
    {

    }

    public function pluckURL()
    {
        $query = "SELECT queueID, url FROM queue WHERE tested='0' AND retries < '" . RETRY_LIMIT . "' LIMIT 1";

        return $this->db->SelectSingleRecord($query);

    }


    public function populateQueue()
    {

        foreach ($url AS $url) {
            $queries[] = sprintf("INSERT INTO queue (url, dateAdded) VALUES ('%s', '%s')", $this->db->EscapeString($url), $this->db->EscapeString());
        }
    }


    public function logIssues()
    {
        $issues = $this->rspArray['resultSet'];

        $count = count($issues);

        if ($count < 1) {
            return false;
        }

        for ($x = 0; $x < $count; $x++) {
            $queries[] = sprintf("INSERT INTO(responseID, url, tID, resultTitle, errorTitle, errorDescription, standards,
                    errorSnippet, line, column, xpath, certainty, priority, ref, signature) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s',
                    '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $this->db->EscapeString($this->rspArray['request']['responseID']),
                $this->db->EscapeString($this->opts['url']),
                $this->db->EscapeString($issues[$x]['tID']),
                $this->db->EscapeString($issues[$x]['resultTitle']),
                $this->db->EscapeString($issues[$x]['errorTitle']),
                $this->db->EscapeString($issues[$x]['errorDescription']),
                $this->db->EscapeString(implode(', ', $issues[$x]['standards'])),
                $this->db->EscapeString(html_entity_decode($issues[$x]['errorSnippet'])),
                $this->db->EscapeString($issues[$x]['position']['line']),
                $this->db->EscapeString($issues[$x]['position']['column']),
                $this->db->EscapeString($issues[$x]['xpath']),
                $this->db->EscapeString($issues[$x]['certainty']),
                $this->db->EscapeString($issues[$x]['priority']),
                $this->db->EscapeString($issues[$x]['ref']),
                $this->db->EscapeString($issues[$x]['signature']));
        }
    }
} 