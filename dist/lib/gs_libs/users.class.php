<?php

/**
 *
 * Class for managing users and authenticating them
 *
 */
class users extends CRUDadminPDO
{

    public $connection;

    /**
     *
     *  Class constructor
     *
     * @param   array $connection database connection details
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        parent::__construct($connection['dbname'], $connection['username'], $connection['password']);
    }

    /**
     *
     * Adds a new user record to the database
     *
     * @param   array $data associative array of data to be inserted
     *
     * @return  bool
     */
    public function insert($data)
    {
        return $this->insert($data, 'users');
    }

    /**
     *
     * Updates a user's database record
     *
     * @param   array $data associative array of data to be updated
     * @param   int   $id   primary key ID of the record to be updated
     *
     * @return  bool
     */
    public function update($data, $id)
    {
        return $this->update($data, 'users', 'userID', $id);
    }

    /**
     *
     * Marks a user record as deleted
     *
     * @param   int $id primary key ID of the record to be marked as deleted
     *
     * @return  bool
     */
    public function delete($id)
    {
        $data = array('deleted' => '1');

        return $this->update($data, 'users', 'userID', $id);
    }

    /**
     *
     * Removes a user record from the database
     *
     * @param   int $id primary key ID of the record to be deleted
     *
     * @return  bool
     */
    public function realDelete($id)
    {
        return $this->delete('users', 'userID', $id);
    }

    /**
     *
     * Retrieves a user record based on PKID
     *
     * @param   int    $id     primary key ID of the record to be retrieved
     * @param   string $fields database fields to be retrieved in the query
     *
     * @return  array
     */
    public function getByID($id, $fields = ' * ')
    {
        $db = new CRUDadminPDO($this->connection['dbName'], $this->connection['user'], $this->connection['pass'], $this->connection['opts'], $this->connection['dbType'], $this->connection['hostORpath'], $this->connection['port']);

        return $db->getByID('users', 'userID', $id, $fields);
    }

    /**
     *
     * Retrieves a user record based on associated key
     *
     * @param   string $key    key string of the record to be retrieved
     * @param   string $fields database fields to be retrieved in the query
     *
     * @return  array
     */
    public function getByKey($key, $fields = ' * ')
    {
        return $this->getByID('users', 'userKey', $key, $fields);

    }

    /**
     *
     * Gets all user records (subject to optional criteria)
     *
     * @param   string $fields
     * @param   string $criteriaField
     * @param   string $criteriaValue
     * @param   string $extras
     * @param   string $orderBy
     * @param   string $orderDir
     * @param   int    $limit
     *
     * @return  array
     */
    public function getAll($fields = ' * ', $criteriaField = 'deleted', $criteriaValue = '0', $extras = null, $orderBy = 'userEmail', $orderDir = 'ASC', $limit = null)
    {
        return $this->getAll('users', $fields, $criteriaField, $criteriaValue, $extras, $orderBy, $orderDir, $limit);
    }

    /**
     *
     * Retrieves a user record based on email address
     *
     * @param   string $email
     * @param   string $fields    database fields to be retrieved in the query
     * @param   bool   $checkOrig whether or not to include the user's original email as well
     *
     * @return  array
     */
    public function getUserByEmail($email, $fields = ' * ', $checkOrig = false)
    {
        $query = sprintf("SELECT %s FROM users WHERE userEmail='%s' ", $this->EscapeString($fields), $this->EscapeString($email));

        if (true === $checkOrig) {
            $query .= sprintf("OR origEmail='%s'", $this->EscapeString($email));
        }

        $query .= " LIMIT 1 ";

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     *
     * Retrieves a user record based on login credentials (email address and password)
     *
     * @param   string $email
     * @param   string $pw
     * @param   string $fields database fields to be retrieved in the query
     *
     * @return  array
     */
    public function getUserByLogin($email, $pw, $fields = ' * ')
    {
        $query = sprintf("SELECT %s FROM users WHERE userEmail='%s' AND userPassword='%s' LIMIT 1", $this->EscapeString($fields), $this->EscapeString($email), $this->EscapeString($pw));

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     *
     * Determines whether or not a user has confirmed their registration based on their key string value
     *
     * @param   string $key
     *
     * @return  bool
     */
    public function userIsConfirmedByKey($key)
    {
        $query = sprintf("SELECT userID FROM users WHERE userKey='%s' AND userConfirm='1' LIMIT 1", $this->EscapeString($key));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }

    /**
     *
     * Determines whether or not a user has confirmed their registration based on their PKID
     *
     * @param   string $id primary key ID of the record to be looked at
     *
     * @return  bool
     */
    public function userIsConfirmedByID($id)
    {
        $query = sprintf("SELECT userKey FROM users WHERE userID='%s' AND userConfirm='1' LIMIT 1", $this->EscapeString($id));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }

    /**
     *
     * Marks a user as 'confirmed' based on their PKID
     *
     * @param   string $id primary key ID of the record to be looked at
     *
     * @return  bool
     */
    public function confirmUserByID($id)
    {
        $query = sprintf("UPDATE users SET userConfirm='1' WHERE userID='%s'", $this->EscapeString($id));

        return $this->UpdateData($query);
    }

    /**
     *
     * Marks a user as 'confirmed' based on their key string
     *
     * @param   string $key
     *
     * @return  bool
     */
    public function confirmUserByKey($key)
    {
        $query = sprintf("UPDATE users SET userConfirm='1' WHERE userKey='%s'", $this->EscapeString($key));

        return $this->UpdateData($query);
    }

    /**
     *
     * Marks a user as 'banned' based on their key string
     *
     * @param   string $key
     *
     * @return  bool
     */
    public function banUserByKey($key)
    {
        $query = sprintf("UPDATE users SET userPermissions='4' WHERE userKey='%s'", $this->EscapeString($key));

        return $this->UpdateData($query);
    }

    /**
     *
     * Marks a user as 'banned' based on their PKID
     *
     * @param   string $id primary key ID of the record to be banned
     *
     * @return  bool
     */
    public function banUserByID($id)
    {
        $query = sprintf("UPDATE users SET userPermissions='4' WHERE userID='%s'", $this->EscapeString($id));

        return $this->UpdateData($query);
    }

    /**
     *
     * Determines whether or not a user is banned, based on their PKID
     *
     * @param   string $id primary key ID of the record to be looked at
     *
     * @return  bool
     */
    public function isUserBannedByID($id)
    {
        $query = sprintf("SELECT userID FROM users WHERE userID='%s' AND userPermissions='4' LIMIT 1", $this->EscapeString($id));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }

    /**
     *
     * Determines whether or not a user is banned, based on their key string
     *
     * @param   string $key
     *
     * @return  bool
     */
    public function isUserBannedByKey($key)
    {
        $query = sprintf("SELECT userID FROM users WHERE userKey='%s' AND userPermissions='4' LIMIT 1", $this->EscapeString($key));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }

    /**
     *
     * Determines whether or not a user is banned, based on their email
     *
     * @param   string $email
     *
     * @return  bool
     */
    public function isUserBannedByEmail($email)
    {
        $query = sprintf("SELECT userID FROM users WHERE (userEmail='%s' OR origEmail='%s') AND userPermissions='4' LIMIT 1", $this->EscapeString($email), $this->EscapeString($email));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }

    /**
     *
     * Determines whether or not a user is banned, based on their email and password (probably redundant and unneeded)
     *
     * @param   string $email
     * @param   string $pw
     *
     * @return  bool
     */
    public function isUserBannedByLogin($email, $pw)
    {
        $query = sprintf("SELECT userID FROM users WHERE userEmail='%s' AND userPassword='%s' AND userPermissions='4' LIMIT 1", $this->EscapeString($email), $this->EscapeString($pw));
        if (false === $this->SelectData($query, PDO::FETCH_ASSOC)) {
            return false;
        }

        return true;
    }

    /**
     *
     * Gets a count of all unbanned, confirmed, non-deleted members from the database
     *
     * @return  int
     */
    function countMembers()
    {
        $query = "SELECT count(*) AS count FROM users  WHERE userPermissions < '4' AND userConfirm='1' AND deleted='0' ";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        return $data[0]['count'];
    }

    /**
     *
     * Gets a human readable representation of a user's permissions level
     *
     * @param   string $val
     *
     * @return  string
     */
    function userPermissionsReadable($val)
    {
        if ($val == 4) {
            return '<em>' . _('BANNED') . '</em>';
        } elseif ($val == 3) {
            return _('Admin');
        } else {
            return _('Regular User');
        }
    }

}
