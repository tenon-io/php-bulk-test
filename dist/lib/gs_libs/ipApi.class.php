<?php

/**
 * Class ipApi
 * Queries http://ip-api.com/ for info on an IP address
 */
class ipApi
{

    protected $apiURL, $dbTable;


    /**
     * @param null   $dbInfo
     * @param string $dbTable
     */
    public function __construct($dbInfo = null, $dbTable = 'ipInfo')
    {
        $this->apiURL = 'http://ip-api.com/json/';
        $this->db = new dbPDO($dbInfo['dbName'], $dbInfo['user'], $dbInfo['pass'],
            $dbInfo['opts'], $dbInfo['dbType'], $dbInfo['hostORpath'], $dbInfo['port']);
        $this->dbTable = $dbTable;
    }

    /**
     *
     */
    public function __destruct()
    {
        $vars = get_object_vars($this);
        if (is_array($vars)) {
            foreach ($vars as $key => $val) {
                $this->$key = null;
            }
        }
    }

    /**
     * @param $ip
     *
     * @return mixed
     */
    public function getIpInfoFromDB($ip)
    {
        $query = sprintf("SELECT * FROM %s WHERE q='%s' LIMIT 1", $this->db->EscapeString($this->dbTable), $this->db->EscapeString($ip));

        return $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

    }


    /**
     * @param      $ip
     * @param bool $getLocal
     * @param bool $printInfo
     *
     * @return mixed|string
     */
    public function getIpInfo($ip, $getLocal = true, $printInfo = false)
    {
        //double check that the IP
        if ((false !== $getLocal) && ($this->ipExists($ip))) {
            return $this->getIpInfoFromDB($ip);
        }

        $url = $this->apiURL . $ip;

        $result = file_get_contents($url);

        return json_decode($result, true);
    }

    /**
     * @param $ip
     *
     * @return mixed
     */
    public function ipExists($ip)
    {
        $query = sprintf("SELECT q FROM %s WHERE q='%s' LIMIT 1", $this->db->EscapeString($this->dbTable), $this->db->EscapeString($ip));

        return $this->db->SelectSingleRecord($query, PDO::FETCH_ASSOC);

    }

    /**
     * @param $data
     *
     * @return bool|mixed
     */
    public function logToDB($data)
    {

        $query = sprintf("INSERT INTO %s(asID, city, country, countryCode, isp, lat, lon, org, q, region, regionName, status, timezone, zip, dateAdded)
                VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $this->db->EscapeString($this->dbTable),
            $this->db->EscapeString($data['as']),
            $this->db->EscapeString($data['city']),
            $this->db->EscapeString($data['country']),
            $this->db->EscapeString($data['countryCode']),
            $this->db->EscapeString($data['isp']),
            $this->db->EscapeString($data['lat']),
            $this->db->EscapeString($data['lon']),
            $this->db->EscapeString($data['org']),
            $this->db->EscapeString($data['query']),
            $this->db->EscapeString($data['region']),
            $this->db->EscapeString($data['regionName']),
            $this->db->EscapeString($data['status']),
            $this->db->EscapeString($data['timezone']),
            $this->db->EscapeString($data['zip']),
            $this->db->EscapeString(Date::UTC2MySQLDateTime())
        );

        return $this->db->InsertData($query);
    }
} 