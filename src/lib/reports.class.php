<?php

/**
 * Class reports gets high-level stats on all of the tests run by the bulk tester
 */
class reports
{

    /**
     * @param $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->init();
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
     * @return array|bool
     */
    public function getDistinctTID()
    {
        $query = "SELECT DISTINCT tID FROM issues";
        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output[] = $data[$x]['tID'];
        }

        return $output;
    }

    /**
     * @return bool
     */
    public function countIssuesByDistinctTID()
    {
        // get list of distinct tID
        $tIDs = $this->getDistinctTID();

        if (false === $tIDs) {
            return false;
        }

        // foreach distinct signature get a count of how many issues
        foreach ($tIDs AS $t) {
            $query = sprintf("SELECT COUNT(*) AS count, errorTitle  FROM issues WHERE tID='%s'", $t);

            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            $output[$data['errorTitle']] = $data['count'];
        }

        return $output;
    }

    /**
     * @return mixed
     */
    public function countAvgErrorsPerPage()
    {
        $query = "SELECT AVG(errors) AS avgErrors FROM responseLog";

        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data['avgErrors'];
    }

    /**
     * @return mixed
     */
    public function countAvgWarningsPerPage()
    {
        $query = "SELECT AVG(warnings) AS avgWarnings FROM responseLog";

        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data['avgWarnings'];
    }

    /**
     * @return mixed
     */
    public function countAvgIssuesPerPage()
    {
        $query = "SELECT AVG(errors + warnings) AS avgIssues FROM responseLog";

        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data['avgIssues'];
    }


    /**
     * @return array|bool
     */
    public function getDistinctSignatures()
    {
        $query = "SELECT DISTINCT signature FROM issues";
        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output[] = $data[$x]['signature'];
        }

        return $output;
    }


    /**
     * @return bool
     */
    public function countIssuesByDistinctSignatures()
    {
        // get list of distinct signatures
        $signatures = $this->getDistinctSignatures();

        if (false === $signatures) {
            return false;
        }

        // foreach distinct signature get a count of how many issues
        foreach ($signatures AS $sig) {
            $query = sprintf("SELECT COUNT(*) AS count FROM issues WHERE signature='%s'", $sig);
            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            $output[$sig] = $data['count'];
        }

        return $output;
    }


    /**
     * @return bool
     */
    public function countDuplicateIssuesBySignatures()
    {
        // get list of distinct signatures
        $signatures = $this->getDistinctSignatures();

        if (false === $signatures) {
            return false;
        }

        // foreach distinct signature get a count of how many issues
        foreach ($signatures AS $sig) {
            $query = sprintf("SELECT COUNT(*) AS count, errorTitle FROM issues WHERE signature='%s'", $sig);
            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            if ($data['count'] > 1) {
                $output[$data['errorTitle']] = $data['count'];
            }
        }

        return $output;
    }


    /**
     * @return mixed
     */
    public function countIssuesByWCAGLevel()
    {
        $levels[] = 'Level AAA:';
        $levels[] = 'Level AA:';
        $levels[] = 'Level A:';

        //foreach wcag level get a count of how many issues
        foreach ($levels AS $l) {
            $query = "SELECT COUNT(*) AS count FROM issues WHERE standards LIKE '%" . $l . "%'";
            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            $output[$l] = $data['count'];
        }

        return $output;

    }

    /**
     * @return mixed
     */
    public function countIssuesByWCAGSC()
    {
        $criterion = array('1.1.1', '1.2.1', '1.2.2', '1.2.3', '1.2.4', '1.2.5', '1.2.6', '1.2.7', '1.2.8', '1.2.9', '1.3.1', '1.3.2',
            '1.3.3', '1.4.1', '1.4.2', '1.4.3', '1.4.4', '1.4.5', '1.4.6', '1.4.7', '1.4.8', '1.4.9', '2.1.1', '2.1.2', '2.1.3',
            '2.2.1', '2.2.2', '2.2.3', '2.2.4', '2.3.1', '2.3.2', '2.4.1', '2.4.2', '2.4.3', '2.4.4', '2.4.5', '2.4.6', '2.4.7',
            '2.4.8', '2.4.9', '2.4.10', '3.1.1', '3.1.2', '3.1.3', '3.1.4', '3.1.5', '3.1.6', '3.2.1', '3.2.2', '3.2.3', '3.2.4',
            '3.2.5', '3.3.1', '3.3.2', '3.3.4', '3.3.5', '3.3.6', '4.1.1', '4.1.2');

        //foreach wcag sc get a count of how many issues
        foreach ($criterion AS $sc) {
            $query = "SELECT COUNT(*) AS count FROM issues WHERE standards LIKE '%" . $sc . "%'";
            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            if ($data['count'] > 0) {
                $output[$sc] = $data['count'];
            }
        }

        return $output;
    }

    /**
     * @return mixed
     */
    public function getAvgCertainty()
    {
        $query = "SELECT AVG(certainty) AS avgCertainty FROM issues";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        return $data['avgCertainty'];
    }

    /**
     * @return mixed
     */
    public function getDistinctCertainty()
    {
        $query = "SELECT DISTINCT (certainty) AS certainty FROM issues";
        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output[] = $data[$x]['certainty'];
        }

        return $output;
    }

    /**
     * @return bool
     */
    public function countIssuesByCertainty()
    {
        // get list of distinct certainty
        $certainties = $this->getDistinctCertainty();

        if (false === $certainties) {
            return false;
        }

        // foreach certainty get a count of how many issues
        foreach ($certainties AS $c) {
            $query = sprintf("SELECT COUNT(*) AS count FROM issues WHERE certainty='%s'", $c);
            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            $output[$c] = $data['count'];
        }

        return $output;
    }

    /**
     * @return mixed
     */
    public function getAvgPriority()
    {
        $query = "SELECT AVG(priority) AS avgPriority FROM issues";
        $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        return $data['avgPriority'];
    }

    /**
     * @return mixed
     */
    public function getDistinctPriority()
    {
        $query = "SELECT DISTINCT(priority) AS priority FROM issues";
        $data = $this->db->SelectData($query, PDO::FETCH_ASSOC);

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output[] = $data[$x]['priority'];
        }

        return $output;
    }

    /**
     * @return bool
     */
    public function countIssuesByPriority()
    {
        $priorities = $this->getDistinctPriority();

        if (false === $priorities) {
            return false;
        }

        // foreach priority get a count of how many issues
        foreach ($priorities AS $p) {
            $query = sprintf("SELECT COUNT(*) AS count FROM issues WHERE priority='%s'", $p);

            $data = $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

            $output[$p] = $data['count'];
        }

        return $output;
    }
}