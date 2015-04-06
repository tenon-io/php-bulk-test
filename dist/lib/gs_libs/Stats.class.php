<?php

/**
 *
 * class for inserting and calculating basic web stats via database
 *
 */
class Stats extends dbPDO
{

    public $Ignore, $connection, $current_user, $tableName;

    /**
     * @param string $connection
     * @param null   $current_user
     * @param string $tableName
     */
    public function __construct($connection, $current_user = null, $tableName = 'stats')
    {
        parent::__construct($connection['dbname'], $connection['username'], $connection['password']);
        $this->connection = $connection;
        $this->current_user = $current_user;
        $this->tableName = $tableName;
    }

    /**
     * Destructor. Unsets all object variables
     */
    public function __destruct()
    {
        $vars = get_object_vars($this);
        if (is_array($vars)) {
            foreach ($vars as $key => $val) {
                $this->$key = null;
            }
        }

        parent::__destruct();
    }

    /**
     *
     * Sets the array of pages to be ignored (IOW not included in stats)
     *
     * @param array $array
     */
    public function setIgnore($array)
    {
        $this->Ignore = $array;
    }

    /**
     *
     * inserts the stats into the database
     *
     * @return bool
     */
    public function insertStats()
    {
        if (in_array($_SERVER['REQUEST_URI'], $this->Ignore)) {
            return false;
        }

        $query = "
            INSERT INTO
                " . $this->tableName . "(
                    stat_date,
                    session_id,
                    stat_url,
                    http_host,
                    http_user_agent,
                    http_accept,
                    user_language,
                    request_method,
                    http_connection,
                    http_referer,
                    remote_addr,
                    request_uri,
                    page,
                    server_protocol,
                    user_id)
            VALUES(
                '" . Date::UTC2MySQLDateTime() . "',
                '" . $this->EscapeString(session_id()) . "',
                '" . $this->EscapeString("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "',
                '" . $this->EscapeString($_SERVER['HTTP_HOST']) . "',
                '" . $this->EscapeString($_SERVER['HTTP_USER_AGENT']) . "',
                '" . $this->EscapeString($_SERVER['HTTP_ACCEPT']) . "',
                '" . $this->EscapeString($_SERVER['HTTP_ACCEPT_LANGUAGE']) . "',
                '" . $this->EscapeString($_SERVER['REQUEST_METHOD']) . "',
                '" . $this->EscapeString($_SERVER['HTTP_CONNECTION']) . "',
                '" . $this->EscapeString($_SERVER['HTTP_REFERER']) . "',
                '" . $this->EscapeString(Network::getip()) . "',
                '" . $this->EscapeString($_SERVER['REQUEST_URI']) . "',
                '" . $this->EscapeString($_SERVER['PHP_SELF']) . "',
                '" . $this->EscapeString($_SERVER['SERVER_PROTOCOL']) . "',
                '" . $this->EscapeString($this->current_user['user_id']) . "')";

        return $this->InsertData($query);
    }

    /**
     *
     * gets the total number of page views (optionally: within a specified range)
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalPageViews($start = null, $end = null)
    {
        $query = "SELECT count(*) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * gets the total number of distinct sessions (optionally: within a specified range)
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalDistinctSessions($start = null, $end = null)
    {
        $query = "SELECT count(DISTINCT session_id) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * gets the total number of distinct IP addresses (optionally: within a specified range)
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalDistinctIPs($start = null, $end = null)
    {
        $query = "SELECT count(DISTINCT remote_addr) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * gets the total number of distinct URIs (optionally: within a specified range)
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalDistinctURIs($start = null, $end = null)
    {
        $query = "SELECT count(DISTINCT request_uri) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * gets the total number of distinct user agents (optionally: within a specified range)
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalDistinctUserAgents($start = null, $end = null)
    {
        $query = "SELECT count(DISTINCT http_user_agent) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * gets the total number of distinct pages (optionally: within a specified range)
     *  The 'page' column is derived from $_SERVER['PHP_SELF'] which means it
     *  only gets the actual page that was viewed, without the query string variables
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalDistinctPages($start = null, $end = null)
    {
        $query = "SELECT count(DISTINCT page) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * gets the total number of distinct users (optionally: within a specified range)
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function getTotalDistinctUsers($start = null, $end = null)
    {
        $query = "SELECT count(DISTINCT user_id) AS count FROM " . $this->tableName;

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= " WHERE stat_date BETWEEN '$start' AND '$end' ";
        }

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[0]['count'];
        }
    }

    /**
     *
     * counts the number of days between to dates
     *
     * @param   string $start start date
     * @param   string $end   end date
     *
     * @return int
     */
    public function countDates($start, $end)
    {
        // make timestamps from the MySQL formatted time
        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);

        // First we need to break these dates into their constituent parts:
        $gd_a = getdate($startTimestamp);
        $gd_b = getdate($endTimestamp);

        // Now recreate these timestamps, based upon noon on each day
        // The specific time doesn't matter but it must be the same each day
        $a_new = mktime(12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year']);
        $b_new = mktime(12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year']);

        // Subtract these two numbers and divide by the number of seconds in a
        //  day. Round the result since crossing over a daylight savings time
        //  barrier will cause this time to be off by an hour or two.
        return round(abs($a_new - $b_new) / 86400);
    }

    /**
     *
     * gets the first date logged in the stats
     *
     * @return  string
     */
    public function getFirstDate()
    {
        $query = "SELECT MIN(stat_date) AS first FROM " . $this->tableName;
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        } else {
            return $data[0]['first'];
        }
    }

    /**
     *
     * gets the last date logged in the stats
     *
     * @return  string
     */
    public function getLastDate()
    {
        $query = "SELECT MAX(stat_date) AS last FROM " . $this->tableName;
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        } else {
            return $data[0]['last'];
        }
    }

    /**
     *  gets complete raw stats for the range identified
     *  CAUTION!!! Keeping $start and $end as NULL will result in far too much data
     *  in most instances
     *
     * @param   string $start start date
     * @param   string $end   end date
     * @param   string $fields
     *
     * @return  array
     */
    public function getRawStats($start = null, $end = null, $fields = '*')
    {
        $query = sprintf("SELECT %s FROM " . $this->tableName, $this->EscapeString($fields));

        if ((!is_null($start)) && (!is_null($end))) {
            $query .= sprintf(" WHERE stat_date BETWEEN '%s' AND '%s' ", $start, $end);
        }

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }
}