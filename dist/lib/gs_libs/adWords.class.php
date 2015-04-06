<?php

/**
 *
 * Class holding a number of functions which helps the site work more effectively with AdWords
 *
 * Referrals: adWords (date_added, src, medium, campaign, network, kw, adID, cKey)
 * Content: adWordsContent(adWordsContentID, adWordsContentTitle, adWordsContent)
 *
 */
class adWords extends dbPDO
{

    public $connection;
    public $expectedAWGet;
    public $urlParams;

    /**
     *
     * @param array $connection database connection details
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        parent::__construct($connection['dbname'], $connection['username'], $connection['password']);
        $this->setExpectedGet();
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
     */
    public function setExpectedGet()
    {
        $this->expectedAWGet = array('src', 'medium', 'campaign', 'network', 'kw', 'adID');
    }

    /**
     *
     * www.domain.com/index.php?adID=1
     * &kw={keyword}src=AdWords
     * &medium=PPC
     * &campaign=default
     * &Network={ifContent:Content}{ifSearch:Search}
     *
     */
    public function getURLParams()
    {
        $this->setExpectedGet();

        foreach ($_GET as $key => $val) {
            if (in_array($key, $this->expectedAWGet)) {
                $this->urlParams[$key] = $val;
            }
        }
    }

    /**
     * @return mixed
     */
    public function insertAWReferral()
    {
        $this->getURLParams();

        Cookie::set('gRefDate', date("Y-m-d H:i:s"));
        Cookie::set('cKey', $this->urlParams['cKey']);

        $query = sprintf("INSERT INTO adWords (date_added, src, medium, campaign, network, kw, adID, cKey) 
                          VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $this->EscapeString(Date::UTC2MySQLDateTime()),
            $this->EscapeString($this->urlParams['src']),
            $this->EscapeString($this->urlParams['medium']),
            $this->EscapeString($this->urlParams['campaign']),
            $this->EscapeString($this->urlParams['network']),
            $this->EscapeString($this->urlParams['kw']),
            $this->EscapeString($this->urlParams['adID']),
            $this->EscapeString(Strings::RandomString(32)));

        return $this->InsertData($query);
    }

    /**
     * @param string $fields
     * @param null   $start
     * @param null   $end
     * @param null   $limit
     *
     * @return mixed
     */
    public function getAWReferrals($fields = ' * ', $start = null, $end = null, $limit = null)
    {
        $query = sprintf("SELECT %s FROM adWords", $fields);

        if ((!is_null($start)) && (is_null($end))) {
            $query .= sprintf(" WHERE date_added > '%s' ", $start);
        }

        if ((!is_null($start)) && (is_null($end))) {
            $query .= sprintf(" WHERE date_added BETWEEN '%s' AND '%s' ", $start, $end);
        }

        if (!is_null($limit)) {
            $query .= sprintf(" LIMIT %s ", $limit);
        }

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param   array $data
     *
     * @return  bool
     */
    public function insertAdwordsContent($data)
    {
        $query = sprintf("
                    INSERT INTO 
                        adWordsContent(
                            adWordsContentTitle
                            adWordsContent
                        ) 
                    VALUES(
                            '%s', 
                            '%s')", $this->EscapeString($data['adWordsContentTitle']), $this->EscapeString($data['adWordsContent']));

        return $this->InsertData($query);
    }

    /**
     *
     * @param   array $data
     *
     * @return  bool
     */
    public function updateAdwordsContent($data)
    {
        $query = sprintf("
                    UPDATE 
                        adWordsContent 
                    SET
                        adWordsContentTitle='%s'
                        adWordsContent='%s'
                    WHERE
                        adWordsContentID='%s'", $this->EscapeString($data['adWordsContentTitle']), $this->EscapeString($data['adWordsContent']), $this->EscapeString($data['adWordsContentID']));

        return $this->UpdateData($query);
    }

    /**
     *
     * @param   int $id
     *
     * @return  array
     */
    public function getAdwordsContentByID($id)
    {
        $query = sprintf("SELECT * FROM adWordsContent WHERE adWordsContentID='%s' LIMIT 1", $this->EscapeString($id));

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param   int $id
     *
     * @return  string
     */
    public function getAdwordsContentHTML($id)
    {
        $content = $this->getAdwordsContentByID($id);

        if (false === $content) {
            return '';
        }

        $output = "<div id=\"adWordsContent\">";
        $output .= "<h2>" . $content[0]['adWordsContentTitle'] . "</h2>";
        $output .= "<p>" . $content[0]['adWordsContent'] . "</p>";
        $output .= "</div>";

        return $output;
    }

    /**
     *
     * @param   int $id
     *
     * @return  bool
     */
    public function deleteAdwordsContent($id)
    {
        $query = sprintf("DELETE FROM adWordsContent WHERE adWordsContentID='%s'", $this->EscapeString($id));

        return $this->DeleteData($query);
    }

    /**
     * @param        $id
     * @param string $fields
     * @param null   $start
     * @param null   $end
     * @param null   $limit
     *
     * @return mixed
     */
    public function getAWReferralsByID($id, $fields = ' * ', $start = null, $end = null, $limit = null)
    {
        $query = sprintf("SELECT %s FROM adWords WHERE adID='%s'", $fields, $this->EscapeString($id));

        if ((!is_null($start)) && (is_null($end))) {
            $query .= sprintf(" WHERE date_added > '%s' ", $start);
        }

        if ((!is_null($start)) && (is_null($end))) {
            $query .= sprintf(" WHERE date_added BETWEEN '%s' AND '%s' ", $start, $end);
        }

        if (!is_null($limit)) {
            $query .= sprintf(" LIMIT %s ", $limit);
        }

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

}