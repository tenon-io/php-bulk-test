<?php

/**
 *
 * Class for performing user authentication tasks such as logging in the users etc.
 *
 */
class auth extends dbPDO
{

    public $connection, $enforceUAString, $sessKeyName, $sessKeyReqVal, $sessTokenName, $loginURL, $logoutURL, $timeLimit,
        $allowRememberMe, $trackUsersOnline, $sessBannedVal, $loggedInLocation, $nonAuthLocation, $userRecord;

    /**
     *
     * @param   array $connection
     */
    public function __construct($connection)
    {
        parent::__construct($connection['dbName'], $connection['user'], $connection['pass'], $connection['opts'], $connection['dbType'], $connection['hostORpath'], $connection['port']);
        $this->connection = $connection;
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
     * sessTokenName - key for the session token
     * sessKeyName - the session key name used to check whether or not someone's logged in
     * sessKeyReqVal - value required for the sessKeyName in order for the user to be considered "logged in"
     * loginURL - path to URL people must go to in order to login
     * logoutURL - path to URL people are taken when they log out
     * timeLimit - how long sessions should be kept for
     * enforceUAString - whether or not to enforce validity of the user agent string
     * allowRememberMe - whether or not to allow users to be "remembered" via cookie
     * trackUsersOnline - whether or not to track users who are online
     * sessBannedVal - the permissions value for someone who has been banned
     * loggedInLocation - url to place where people are taken once they're logged in
     * nonAuthLocation - url to place where people are taken if they're not authorized to access a resource
     *
     * @param array $array
     */
    public function config($array)
    {
        foreach ($array AS $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     *
     * redirects users to a new location (intended to be used on non-authorized users)
     *
     * @param   string $where location where people will be sent
     *
     * @return  void
     */
    public function nonAuthRedirect($where = null)
    {
        if (is_null($where)) {
            $where = $this->nonAuthLocation;
        }
        header("Location:" . $where);
        exit;
    }

    /**
     *
     * prevent session hijacking by logging someone out if their user agent string
     * is different from the user agent string they had when they logged in
     */
    public function enforceUA()
    {
        if ($this->enforceUAString == true) {
            // if this session variable is set, check it
            if (isset($_SESSION['_guser_agent'])) {

                // no match? Log them out automatically
                if ($_SESSION['_guser_agent'] != $_SERVER['HTTP_USER_AGENT']) {
                    $_SESSION[$this->sessKeyName] = false;
                }

                // if it isn't set, set it
            } else {
                $_SESSION['_guser_agent'] = $_SERVER['HTTP_USER_AGENT'];
            }
        }
    }

    /**
     * check to ensure the user is authorized
     */
    public function checkAuthorization()
    {
        // (optionally) enforce user agent string to check against session hijacking
        $this->enforceUA();

        if ($_SESSION[$this->sessKeyName] != $this->sessKeyReqVal) {
            // see if an existing cookie is found that
            // exactly matches a corresponding record in the connection.
            // if not, redirect them to the login screen.
            if (false === $this->checkRememberMe()) {

                if (false === $this->loggedInLocation) {
                    // get the page location so we can redirect people who aren't logged in
                    $return_url = rawurlencode(trim($_SERVER['REQUEST_URI']));
                    // get the current page
                    // redirect the non-logged in user
                    header("Location: " . Network::AddToQuery("returnto", $return_url, $this->loginURL));
                    exit;
                } else {
                    header("Location: " . $this->loggedInLocation);
                    exit;
                }
            } else {
                return true;
            }
        }

        // just bail
        return false;
    }

    /**
     *
     * Sets the session token
     */
    public function setTokenKey()
    {
        if (!isset($_SESSION[$this->sessTokenName])) {
            $_SESSION[$this->sessTokenName] = md5(uniqid(rand(), true));
        }
    }

    /**
     *
     * @param   int    $id
     * @param   string $fields
     * @param   bool   $showDeleted
     *
     * @return  array
     */
    public function getUserRecordByID($id, $fields = '*', $showDeleted = false)
    {
        $query = sprintf("SELECT %s FROM users WHERE userID='%s' ", $this->EscapeString($fields), $this->EscapeString($id));

        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }

        $query .= " LIMIT 1 ";

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if ($data == false) {
            return false;
        }
        $this->userRecord = $data[0];

        return true;
    }

    /**
     *
     * @param   string $un
     * @param   string $pw
     * @param   bool   $showDeleted
     *
     * @return  bool
     */
    public function userExistsByLogin($un, $pw, $showDeleted = false)
    {
        $query = sprintf("SELECT userID FROM users WHERE userEmail='%s' AND userPassword='%s' ", $this->EscapeString($un), $this->EscapeString($pw));

        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }
        $query .= " LIMIT 1 ";

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param   int  $id
     * @param   bool $showDeleted
     *
     * @return  bool
     */
    public function userExistsByID($id, $showDeleted = false)
    {
        $query = sprintf("SELECT userID FROM users WHERE userID='%s' ", $this->EscapeString($id));
        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }

        $query .= " LIMIT 1 ";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param   string $key
     * @param   bool   $showDeleted
     *
     * @return  bool
     */
    public function userExistsByKey($key, $showDeleted = false)
    {
        $query = sprintf("SELECT userID FROM users WHERE userKey='%s' ", $this->EscapeString($key));
        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }

        $query .= " LIMIT 1 ";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param   string $id
     *
     * @return  bool
     */
    public function userIsBanned($id)
    {
        $query = sprintf("SELECT userID FROM users WHERE userPermissions='%s' AND userID='%s' LIMIT 1", $this->EscapeString($this->sessBannedVal), $this->EscapeString($id));

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if ($data == false) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return  bool
     */
    public function isLoggedIn()
    {
        if ($_SESSION[$this->sessKeyName] == $this->sessKeyReqVal) {
            return true;
        }

        return false;
    }


    /**
     *
     */
    public function logout()
    {
        try {
            $this->deleteCookies();
            $queries[] = sprintf("DELETE FROM usersOnline WHERE sessionID='%s'", $this->EscapeString(session_id()));
            $this->MultiQuery($queries);

            session_start();
            session_unset();
            session_destroy();
            session_write_close();
            setcookie(session_name(), '', 0, '/');
            session_regenerate_id(true);

            header("Location:" . $this->logoutURL);
            exit();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *
     * @param   string $un
     * @param   string $pw
     * @param   bool   $showDeleted
     *
     * @return  bool
     */
    public function userIsConfirmed($un, $pw, $showDeleted = false)
    {
        $query = sprintf("SELECT userID FROM users WHERE userEmail='%s' AND userPassword='%s' AND userConfirm='1' ", $this->EscapeString($un), $this->EscapeString($pw));
        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }

        $query .= " LIMIT 1 ";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param   int $id
     *
     * @return  bool
     */
    public function setRememberMe($id)
    {
        $query = sprintf("SELECT userKey, userTimeout FROM users WHERE userID='%s' LIMIT 1", $this->EscapeString($id));
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        //means we have no user, so no need to 'remember' anything
        if (false === $data) {
            return false;
        }
        $timeout = time() + $data[0]['userTimeout'];

        if (false == Cookie::set('userKey', $data[0]['userKey'], $timeout, '/', $_SERVER['HTTP_HOST'])) {
            //echo 'COULD NOT SET userKey cookie!';
            return false;
        }
        if (false == Cookie::set('userTimeout', $timeout, $timeout, '/', $_SERVER['HTTP_HOST'])) {
            //echo 'COULD NOT SET userTimeout cookie!';
            return false;
        }

        $rememberKey = $this->generateRememberKey($data[0]['userKey'], $timeout);
        if (false == Cookie::set('rememberKey', $rememberKey, $timeout, '/', $_SERVER['HTTP_HOST'])) {
            //echo 'COULD NOT SET rememberKey cookie!';
            return false;
        }

        return true;
    }

    /**
     *
     * @param   string $userKey
     * @param   date   $expire
     *
     * @return  mixed
     */
    public function generateRememberKey($userKey, $expire)
    {
        // do some basic garbage collection first
        $this->gcRememberMe();

        // clean out any remember keys that exist for the user
        $q1 = sprintf("DELETE FROM rememberMe WHERE userKey = '%s'", $this->EscapeString($userKey));
        $this->DeleteData($q1);

        $query = sprintf("INSERT INTO rememberMe(userKey, rememberKey, expire) VALUES('%s', '%s', '%s')", $this->EscapeString($userKey), $this->EscapeString(Strings::RandomString(16)), $this->EscapeString($expire));

        $inserted = $this->InsertData($query);

        // @todo throw exception here
        if (false === $inserted) {
            return false;
        }

        return $randomString;
    }

    /**
     *
     * @param   string $userKey
     * @param   bool   $showDeleted
     *
     * @return  bool
     */
    public function reauthenticateUser($userKey, $showDeleted = false)
    {

        $query = sprintf("SELECT userID, userPassword FROM users WHERE userKey='%s'", $this->EscapeString($userKey));
        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }

        $query .= " LIMIT 1 ";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $this->processLogin($data[0]['userEmail'], $data[0]['userPassword']);
        }
    }


    /**
     * @param $un
     * @param $pw
     *
     * @return bool
     */
    public function processLogin($un, $pw)
    {

        $now = Date::UTC2MySQLDateTime();

        // dump old, expired sessions from the connection
        $this->gcRememberMe();

        // Before we send them onward, let's set some cookies to help them get around
        // Query the connection to get their information
        $query = sprintf("SELECT * FROM users WHERE userEmail = '%s' AND userPassword = '%s' LIMIT 1", $this->EscapeString($un), $this->EscapeString($pw));

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            //echo 'USER NOT FOUND';
            return false;
        } else {
            $this->setTokenKey();

            $_SESSION['dateFormat'] = $data[0]['dateFormat'];

            $_SESSION['userPermissions'] = $data[0]['userPermissions'];

            $_SESSION['timeout'] = $data[0]['userTimeout'];

            $_SESSION[$this->sessKeyName] = $this->sessKeyReqVal;

            $_SESSION['userKey'] = $data[0]['userKey'];

            $_SESSION['userName'] = $data[0]['userName'];

            $_SESSION['userLang'] = $data[0]['userLang'];

            $_SESSION['userEmail'] = $data[0]['userEmail'];

            $_SESSION['emailHash'] = md5($data[0]['userEmail']);

            $_SESSION['_guser_agent'] = $_SERVER['HTTP_USER_AGENT'];
            // set this pas part of preventing session hijacking

            $query2 = sprintf("UPDATE users SET userLastLogin = '%s' WHERE userID = '%s'", $this->EscapeString($now), $this->EscapeString($data[0]['userID']));
            $this->UpdateData($query2);
            if (false !== $this->allowRememberMe) {
                $this->setRememberMe($data[0]['userID']);
            }

            if (false !== $this->trackUsersOnline) {
                $this->UpdateUserOnline($data[0]['userID']);
            }

            return $_SESSION;
        }
    }

    /**
     *
     * @global  string $config_key
     * @return  bool
     */
    public function checkRememberMe()
    {

        if (isset($_COOKIE['userKey'])) {
            $query = sprintf("
            SELECT
                users.userID,
                rememberMe.rememberKey,
                rememberMe.expire,
                rememberMe.userKey
            FROM
                users,
                rememberMe
            WHERE
                users.userKey='%s'
                AND
                rememberMe.rememberKey='%s'
                AND
                rememberMe.expire='%s'
                AND
                rememberMe.userKey=users.userKey
            LIMIT
                1", $this->EscapeString($_COOKIE['userKey']), $this->EscapeString($_COOKIE['rememberKey']), $this->EscapeString($_COOKIE['userTimeout']));

            $data = $this->SelectData($query, PDO::FETCH_ASSOC);

            if (false === $data) {
                return false;
            } else {
                return $this->reauthenticateUser($data[0]['userID']);
            }
        } else {
            return false;
        }
    }

    /**
     *
     * @return void
     */
    public function deleteCookies()
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $this->deleteRememberMe($_COOKIE['rememberKey']);
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time() - (3600 * 25));
                setcookie($name, '', time() - (3600 * 25), '/');
            }
            $_COOKIE = array();
        }
    }

    /**
     *
     * @param   string $key
     *
     * @return  bool
     */
    public function deleteRememberMe($key)
    {
        $query = sprintf("DELETE FROM rememberMe WHERE rememberKey='%s'", $this->EscapeString($key));

        return $this->DeleteData($query);
    }

    /**
     *
     * @return  bool
     */
    public function gcRememberMe()
    {
        $now = Date::futureDate("datetime");
        $query = sprintf("DELETE FROM rememberMe WHERE expire < '%s'", $this->EscapeString($now));

        return $this->DeleteData($query);
    }

    /**
     *
     * @param   int $id
     *
     * @return  bool
     */
    public function gcUsersOnline($id)
    {
        $query = sprintf("DELETE FROM usersOnline WHERE userID='%s'", $this->EscapeString($id));

        return $this->DeleteData($query);
    }

    /**
     *
     * @param   int $id
     *
     * @return  bool
     */
    public function updateUserOnline($id)
    {
        $now = Date::UTC2MySQLDateTime();

        // DUMP ENTRIES TOUCHED MORE THAN 30 MINUTES AGO
        // OMG I AM SO GHEY FOR STRTOTIME!!!!!!11
        // @todo make this time business be configurable
        $thirtyMinutesAgo = date("Y-m-d H:i:s", strtotime("30 minutes ago"));
        $query = sprintf("DELETE FROM usersOnline WHERE lastTouch < '%s'", $this->EscapeString($thirtyMinutesAgo));
        $this->DeleteData($query);

        $query1 = sprintf("SELECT userID FROM usersOnline WHERE userID='%s' LIMIT 1", $this->EscapeString($id));
        if (false === $this->SelectData($query1)) {
            $query2 = sprintf("INSERT INTO usersOnline(userID, sessionID, lastTouch) VALUES('%s', '" . session_id() . "', '%s')", $this->EscapeString($id), $this->EscapeString($now));

            return $this->InsertData($query2);
        } else {
            $query2 = sprintf("UPDATE usersOnline SET lastTouch='%s' WHERE userID='%s'", $this->EscapeString($now), $this->EscapeString($id));

            return $this->UpdateData($query2);
        }
    }

    /**
     *
     * @param string $key
     * @param   bool $showDeleted
     *
     * @return array
     */
    public function getUserRecordByKey($key = '', $showDeleted = false)
    {
        if ($key == '') {
            $key = $_SESSION['userKey'];
        }

        $query = sprintf("SELECT * FROM users WHERE userKey='%s' ", $this->EscapeString($key));
        if (false === $showDeleted) {
            $query .= " AND deleted='0' ";
        }

        $query .= " LIMIT 1 ";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        }

        return $data[0];
    }

    /**
     * @param $level
     *
     * @return bool
     */
    public function checkPermissions($level)
    {
        $query = sprintf("SELECT userID FROM users WHERE userKey='%s' AND userPermissions >= '%s' LIMIT 1", $this->EscapeString($_SESSION['loggedUserKey']), $this->EscapeString($level));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }
}