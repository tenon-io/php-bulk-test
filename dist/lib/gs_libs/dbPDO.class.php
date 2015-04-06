<?php

/**
 *
 * Class for performing basic database tasks with PDO.
 *
 * NOTE:  Although this uses PDO, a handful of methods in this class will probably only work with MySQL
 *        particularly those which retrieve information _about_ tables such as getPrimaryKey, etc.
 *        All methods that Insert/ Read/ Update/ Delete data should be pretty database agnostic.
 *
 * NOTE:  At the moment this class has ONLY been tested on MySQL
 *
 * TO SET NAMES, USE ON THE $opts PARAM OF THE CONSTRUCTOR:
 *  array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
 *
 */
class dbPDO
{

    public $dbName, $user, $pass, $opts, $connStr, $db, $dbType;
    public $lastQuery;

    /**
     * class constructor
     *
     * @param   string $dbName     database name
     * @param   string $user       database username
     * @param   string $pass       database password
     * @param   array  $opts       connection options.
     * @param   string $dbType     type of database - must be one of the following: mysql, sqlite, pgsql, firebird, informix, oracle, odbc, dblib, ibm
     * @param   string $hostORpath ip or name of host (in the case of mysql or pgsql) or file path to db (in case of sqlite, firebird)
     * @param   string $port       additional param where you can specify the port
     *
     * @throws PDOException
     */
    public function __construct($dbName, $user, $pass, $opts = null, $dbType = 'mysql', $hostORpath = 'localhost', $port = '')
    {

        $this->dbName = $dbName;
        $this->user = $user;
        $this->pass = $pass;
        $this->opts = $opts;
        $this->dbType = $dbType;
        $this->makeLog = false;

        switch (strtoupper($dbType)) {
            case strtoupper('MYSQL') :
                $this->connStr = 'mysql:host=' . $hostORpath . ';dbname=' . $dbName;
                break;

            case strtoupper('SQLITE') :
                $this->connStr = 'sqlite:' . $hostORpath;
                break;

            case strtoupper('PGSQL') :
                $this->connStr = 'pgsql:host=' . $hostORpath . ' dbname=' . $dbName;
                break;

            case strtoupper('FIREBIRD') :
                $this->connStr = 'firebird:dbname=' . $hostORpath;
                break;

            case strtoupper('INFORMIX') :
                $this->connStr = 'informix:DSN=InformixDB';
                break;

            case strtoupper('ORACLE') :
                $this->connStr = 'OCI:dbname=' . $dbName;
                break;

            case strtoupper('ODBC') :
                $this->connStr = 'odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $hostORpath . ';Uid=' . $this->user;
                break;

            case strtoupper('DBLIB') :
                $this->connStr = 'dblib:host=' . $hostORpath . ':' . $port . ';dbname=' . $dbName;
                break;

            case strtoupper('IBM') :
                $this->connStr = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=' . $dbName . '; HOSTNAME=' . $hostORpath;
                $this->connStr .= ';PORT=' . $port . ';PROTOCOL=TCPIP;';
                break;
            default :
                throw new PDOException(_('Invalid Database Type Supplied'));
        }
    }

    /**
     * Destructor. Unsets all object variables and closes the database handle
     */
    public function __destruct()
    {
        $vars = get_object_vars($this);
        if (is_array($vars)) {
            foreach ($vars as $key => $val) {
                $this->$key = null;
            }
        }
        $this->Close();
    }

    /**
     * Connects to PDO
     */
    public function conn()
    {
        if (!$this->db instanceof PDO) {
            if (is_array($this->opts)) {
                $this->db = new PDO($this->connStr, $this->user, $this->pass, $this->opts);
            } else {
                $this->db = new PDO($this->connStr, $this->user, $this->pass);
            }

            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->RawExec('SET CHARACTER SET utf8');

            return true;
        }
    }

    /**
     * Explicitly closes the database handle
     */
    public function Close()
    {
        $this->db = null;
    }

    /**
     * Performs a raw PDO::exec() on a query. Really meant for queries that don't return results
     *
     * @param   string $query the query to be run
     *
     * @return   int returns the number of rows that were modified or deleted by the SQL statement you issued. If no rows were affected, returns 0
     */
    public function RawExec($query)
    {
        $this->lastQuery = $query;
        try {
            $this->conn();
            $output = $this->db->exec($query);
            if (false === $output) {
                return false;
            }

            return $output;
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * performs a PDOStatement::query and fetchAll to run a query that returns results
     *
     * @param   string $query       the query to insert the data
     * @param int      $mode        this is the type of array to be returned.
     * @param    bool  $falseOnZero whether or not to return FALSE when 0 results are returned
     *
     * @return    bool| array
     */
    public function RawQueryFetchAll($query, $mode = PDO::FETCH_ASSOC, $falseOnZero = true)
    {
        $this->lastQuery = $query;
        try {
            $this->conn();
            $stmt = $this->db->query($query);
            $results = $stmt->fetchAll($mode);

            if (true === $falseOnZero) {
                if (count($results) > 0) {
                    return $results;
                } else {
                    return false;
                }
            } else {
                return $results;
            }

        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * performs a PDOStatement::query and fetchAll to run a query that returns results
     *
     * @param   string $query       the query to insert the data
     * @param int      $mode        this is the type of array to be returned.
     * @param    bool  $falseOnZero whether or not to return FALSE when 0 results are returned
     *
     * @return    bool| array
     */
    public function RawQuery($query, $mode = PDO::FETCH_BOTH, $falseOnZero = true)
    {
        $output = array();

        try {
            $this->lastQuery = $query;;
            $this->conn();

            $result = $this->db->query($query);

            while ($row = $result->fetch($mode)) {
                $output[] = $row;
            }

            if ((true == $falseOnZero) && (count($output) == 0)) {
                return false;
            }

            return $output;
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * Creates a database
     *
     * @param   string $database name of the database we're creating
     *
     * @return  bool    TRUE on success, FALSE otherwise
     */
    public function CreateDB($database)
    {
        $query = sprintf("CREATE DATABASE %s", $this->EscapeString($database));

        return $this->RawExec($query);
    }

    /**
     * Runs a generic PDO::exec on a query to create a table
     *
     * @param   string $query the SQL statement used to create the table
     *
     * @return  bool
     */
    public function CreateTable($query)
    {
        return $this->RawExec($query);
    }

    /**
     * Drops a database
     *
     * @param   string $database name of the database we're dropping
     *
     * @return  bool    TRUE on success, FALSE otherwise
     */
    public function DropDB($database)
    {
        $query = sprintf("DROP DATABASE %s", $this->EscapeString($database));

        return $this->RawExec($query);
    }

    /**
     * inserts data into the database using any arbitrary query
     *
     * @param   string $query the query to insert the data
     *
     * @return  mixed   returns FALSE on failure, or the value of the primary key (TRUE if primary key value is 0)
     */
    public function InsertData($query)
    {
        return $this->RawExec($query);
    }

    /**
     * Inserts a new record using an associative array of values
     *
     * @param   array  $data
     * @param   string $table
     *
     * @return  bool
     */
    public function InsertArray($data, $table)
    {
        if (!is_array($data)) {
            return false;
        }

        $cols = implode(',', array_keys($data));
        foreach (array_values($data) as $value) {
            isset($vals) ? $vals .= ',' : $vals = '';
            $vals .= '\'' . $this->EscapeString($value) . '\'';
        }
        $query = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $cols, $vals);

        return $this->InsertData($query);
    }

    /**
     * Like "InsertArray", this method inserts an associative array using a prepared statement
     * NOTE: this method is preferred over InsertArray
     *
     * @param    array  $data  the array of key=>value pairs to be inserted
     * @param    string $table the table into which we'll be performing
     *
     * @return bool
     */
    public function InsertPrepared($data, $table)
    {
        try {
            $this->conn();

            $fields = array_keys($data);

            $query = "INSERT INTO " . $table . " (" . implode(',', $fields) . ")";
            $query .= "VALUES (" . implode(', :', $fields) . ")";

            $stmt = $this->db->prepare($query);
            $this->lastQuery = $query;

            foreach ($fields AS $field) {
                $stmt->bindValue(':' . $field, $data[$field]);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * Uses prepared statement to insert multiple new records using a multidimensional associative array
     * Borrowed from elsewhere. Props to whoever it was (I forgot)
     *
     * @param   string $table  the table we're inserting data into
     * @param   array  $values the array of values to be inserted
     *
     * @return bool
     */
    public function InsertMultiplePrepared($table, $values)
    {
        try {
            $this->conn();

            /*** snag the field names from the first array member ***/
            $fieldnames = array_keys($values[0]);

            /*** now build the query ***/
            $sql = "INSERT INTO $table";

            //*** set the field names ***/
            $fields = '( ' . implode(' ,', $fieldnames) . ' )';

            /*** set the placeholders ***/
            $bound = '(:' . implode(', :', $fieldnames) . ' )';

            /*** put the query together ***/
            $sql .= $fields . ' VALUES ' . $bound;

            $this->lastQuery = $sql;

            /*** prepare and execute ***/
            $stmt = $this->db->prepare($sql);

            foreach ($values as $vals) {
                $stmt->execute($vals);
            }
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }

        return true;
    }


    /**
     * Allows the insert of multiple records into the same table
     *
     * @param $table
     * @param $values
     *
     * @return int
     */
    public function InsertMultiple($table, $values)
    {
        /*** snag the field names from the first array member ***/
        $fieldnames = array_keys($values[0]);

        /*** now build the query ***/
        $output = "INSERT INTO $table";

        //*** set the field names ***/
        $output .= '( ' . implode(' ,', $fieldnames) . ' ) VALUES';

        $count = count($values);
        $i = $count;
        for ($x = 0; $x < $count; $x++) {

            $output .= ' ( ' . $this->makeSQLIN($values[$x]) . ' ) ';

            if ($i > 1) {
                $output .= ',';
            }

            $i--;
        }

        return $this->RawExec($output);
    }

    /**
     * Updates a single record using an associative array of values
     *
     * @param   string $table
     * @param   array  $array
     * @param   string $keyField
     * @param   string $keyVal
     * @param   string $extras
     *
     * @return  bool
     */
    public function UpdateArray($table, $array, $keyField, $keyVal, $extras = null)
    {

        if (!is_array($array)) {
            return false;
        }

        $query = sprintf("UPDATE %s SET", $this->EscapeString($table));

        foreach ($array AS $key => $val) {
            $queryParts[] = sprintf(" $key = '%s' ", $this->EscapeString($val));
        }

        $query .= implode(',', $queryParts);

        $query .= sprintf(" WHERE %s='%s'", $this->EscapeString($keyField), $this->EscapeString($keyVal));

        if (!is_null($extras)) {
            $query .= sprintf(" AND %s ", $this->EscapeString($extras));
        }

        return $this->UpdateData($query);

    }

    /**
     * Like "UpdateArray", this method updates a table using an associative array using a prepared statement
     * NOTE: this method is preferred
     *
     * @param    array  $data  the array of key=>value pairs to be updated
     * @param    string $table the table into which we'll be performing
     * @param           $keyField
     * @param           $keyVal
     *
     * @return bool
     */
    public function UpdatePrepared($data, $table, $keyField, $keyVal)
    {
        try {
            $this->conn();

            $fields = array_keys($data);
            foreach ($fields AS $field) {
                $qParts[] = $field . '=:' . $field;
            }

            $query = "UPDATE" . $table . " SET " . implode(',', $qParts) . " WHERE " . $keyField . "='" . $keyVal . "'";

            $stmt = $this->prepare($query);
            $this->lastQuery = $query;

            foreach ($fields AS $field) {
                $stmt->bindValue(':' . $field, $data[$field]);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * updates data in the database  using any arbitrary query
     *
     * @param   string $query the query to update the data
     *
     * @return bool
     */
    public function UpdateData($query)
    {
        return $this->RawExec($query);
    }

    /**
     *
     * deletes data from the database using any arbitrary query
     *
     * @param   string $query the query to delete the data
     *
     * @return bool
     */
    public function DeleteData($query)
    {
        return $this->RawExec($query);
    }

    /**
     * deletes a specific record from the database
     *
     * @param   string     $table  the table we're deleting a record from
     * @param   string     $field  the criteria field in the table
     * @param   int|string $val    the criteria value for the record to be deleted
     * @param    string    $extras any arbitrary extra criteria for the deletion
     *
     * @return int
     */
    public function DeleteRecord($table, $field, $val, $extras = null)
    {
        $query = sprintf("DELETE FROM %s WHERE %s='%s'", $this->EscapeString($table), $this->EscapeString($field), $this->EscapeString($val));
        if (!is_null($extras)) {
            $query .= $this->EscapeString($extras);
        }

        return $this->RawExec($query);
    }

    /**
     * deletes a specific record from the database using a prepared statement
     *
     * @param   string     $table  the table we're deleting a record from
     * @param   string     $field  the criteria field in the table
     * @param   int|string $val    the criteria value for the record to be deleted
     * @param    string    $extras any arbitrary extra criteria for the deletion
     *
     * @return  bool
     */
    public function DeletePrepared($table, $field, $val, $extras = null)
    {
        try {

            $this->conn();

            $query = "DELETE FROM" . $table . " WHERE " . $field . "=:" . $field;

            if (!is_null($extras)) {
                $query .= $this->EscapeString($extras);
            }

            $stmt = $this->prepare($query);
            $this->lastQuery = $query;

            $stmt->bindValue(':' . $field, $val);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * selects data from the database using any arbitrary query
     *
     * @param   string   $query the query to insert the data
     * @param int|string $mode  this is the type of array to be returned.
     * @param bool       $falseOnZero
     *
     * @return  mixed   returns FALSE on failure, or the array of results on success
     */
    public function SelectData($query, $mode = PDO::FETCH_BOTH, $falseOnZero = true)
    {
        $result = $this->RawQuery($query, $mode, $falseOnZero);

        return $result;

    }

    /**
     * selects one single record from the database using any arbitrary query
     *
     * @param   string $query the query to select the item
     * @param int      $mode  the fetch mode for the selection
     *
     * @return  mixed    returns FALSE on failure, or the single result on success
     */
    public function SelectSingleRecord($query, $mode = PDO::FETCH_BOTH)
    {
        $data = $this->SelectData($query, $mode, true);

        if (false === $data) {
            return false;
        } else {
            return $data[0];
        }
    }

    /**
     * Selects a specific record from the database
     *
     * @param   string $table         the table to select from
     * @param   string $criteriaField the specific field used for the criteria to select the record
     * @param   string $criteriaValue the specific value for the field to select the record
     * @param   string $fields        the fields to retrieve from the query
     * @param   string $extras        any extra stuff for the query
     *
     * @return  array
     */
    public function SelectSpecificRecord($table, $criteriaField, $criteriaValue, $fields = '*', $extras = null)
    {
        $query = sprintf("SELECT %s FROM %s WHERE %s='%s'", $this->EscapeString($fields), $this->EscapeString($table), $this->EscapeString($criteriaField), $this->EscapeString($criteriaValue));

        if (!is_null($extras)) {
            $query .= sprintf(' AND %s', $extras);
        }

        $query .= ' LIMIT 1 ';

        return $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);
    }

    /**
     * Selects data from database using prepared statements.
     * NOTE: This is the preferred method of retrieving data
     * NOTE: This method does not work with LIKE statements
     *
     * @param    string $table    the table from which the data will come
     * @param    array  $criteria array of key=>val pairs for selection criteria
     * @param    string $fields   the fields to retrieve from the database
     * @param    int    $limit    limit to the # of results to return
     * @param    string $orderBy  the field to order the results by
     * @param    string $orderDir the direction to order the results
     *
     * @return    array
     */
    public function SelectPrepared($table, $criteria, $fields = '*', $limit = null, $orderBy = null, $orderDir = null)
    {
        try {
            $this->conn();

            if (is_array($criteria)) {
                $qFields = array_keys($criteria);
                foreach ($qFields AS $qF) {
                    $qParts[] = $qF . '=:' . $qF;
                }
            } else {
                // @TODO We require an array do something smart like throw an exception
            }

            $query = "SELECT " . $fields . " FROM " . $table . " WHERE ";
            $query .= implode(' AND ', $qParts);

            if ((!is_null($orderBy)) && (!is_null($orderDir))) {
                $query .= " ORDER BY " . $orderBy . " " . $orderDir;
            } elseif ((!is_null($orderBy)) && (is_null($orderDir))) {
                $query .= " ORDER BY " . $orderBy;
            }

            if (!is_null($limit)) {
                $query .= " LIMIT " . $limit;
            }

            $stmt = $this->prepare($query);
            $this->lastQuery = $query;

            foreach ($qFields AS $qF) {
                $stmt->bindValue(':' . $qf, $criteria[$qf]);
            }

            if ($stmt->execute()) {
                while ($row = $stmt->fetch()) {
                    $output[] = $row;
                }

                return $output;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     *
     * @param   string  $field
     * @param   string  $table
     * @param   string  $clause
     * @param    string $label
     *
     * @return  int
     */
    public function getSumField($field, $table, $clause = '', $label = null)
    {
        if (is_null($label)) {
            $label = $field;
        }

        $query = sprintf("SELECT SUM(%s) AS %s FROM %s %s", $field, $label, $table, $clause);

        $data = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data[$label];
    }

    /**
     *
     * @param   string  $field
     * @param   string  $table
     * @param   string  $clause
     * @param    string $label
     *
     * @return  int
     */
    public function getAvgField($field, $table, $clause = '', $label = null)
    {
        if (is_null($label)) {
            $label = $field;
        }

        $query = sprintf("SELECT AVG(%s) AS %s FROM %s %s", $field, $label, $table, $clause);
        $data = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data[$label];
    }

    /**
     *
     * @param    string $field
     * @param    string $table
     * @param    string $clause
     * @param null      $label
     *
     * @return  int
     */
    public function getMaxField($field, $table, $clause = '', $label = null)
    {
        if (is_null($label)) {
            $label = $field;
        }

        $query = sprintf("SELECT MAX(%s) AS %s FROM %s %s", $field, $label, $table, $clause);
        $data = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data[$label];
    }

    /**
     *
     * @param    string  $field
     * @param    string  $table
     * @param    string  $clause
     * @param     string $label
     *
     * @return  int
     */
    public function getMinField($field, $table, $clause = '', $label = null)
    {
        if (is_null($label)) {
            $label = $field;
        }

        $query = sprintf("SELECT MIN(%s) AS %s FROM %s %s", $field, $label, $table, $clause);
        $data = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        return $data[$label];
    }

    /**
     * replacement for PDO::quote, this escapes special characters. Also, PDO::quote adds quotes at the beginning
     * and end of strings which is lame and unwanted, so we're not doing that
     *
     * @param   string $string    the string we're working with
     * @param   bool   $stripTags whether or not to strip tags from the input string
     * @param int      $paramType parameter type hint to provide to PDO. See http://php.net/manual/en/pdo.constants.php for more
     *
     * @return  string
     */
    public function EscapeString($string, $stripTags = false, $paramType = PDO::PARAM_STR)
    {

        //strip tags, if so desired
        if ((true == $stripTags) && (!is_array($string))) {
            $string = strip_tags($string);
        }

        // numeric values need no quotes
        if (is_numeric($string)) {
            return trim($string);
        }

        // handle arrays
        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $output[$k] = self::EscapeString(trim($v), $stripTags, $paramType);
            }

            return $output;
        } //actually quote the string
        else {
            // make sure we actually have a PDO instance
            $this->conn();

            return substr($this->db->quote(trim($string), $paramType), 1, -1);
        }

    }

    /**
     * lists all fields in the table
     * NOTE: This is MySQL specific.
     *
     * @param   string $table the table we're looking into
     *
     * @return  mixed   returns FALSE on failure or an array of field names on success
     */
    public function ListFields($table)
    {
        $query = sprintf("SHOW COLUMNS FROM %s", $table);

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     * lists all tables in the database
     * NOTE: This is MySQL specific.
     *
     * @param   string $database the database we're looking into
     *
     * @return  mixed   returns FALSE on failure or an array of tables on success
     */
    public function ListTables($database)
    {
        $query = sprintf("SHOW TABLES FROM %s", $database);
        $data = $this->SelectData($query, PDO::FETCH_NUM);

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output[] = $data[$x][0];
        }

        return $output;

    }

    /**
     * shows the indexes for a particular table
     * NOTE: This is MySQL specific.
     *
     * @param   string $table the table we're looking ag
     *
     * @return  mixed   returns FALSE on failure or an array of tables on success
     */
    public function ShowIndexes($table)
    {
        $query = sprintf("SHOW INDEXES FROM %s", $table);

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     * optimizes all tables in the database
     * NOTE: This is MySQL specific.
     *
     * @param   string $database the database we're working with
     *
     * @return  bool
     */
    public function OptimizeDatabase($database)
    {
        $tables = $this->ListTables($database);
        if (false === $tables) {
            return false;
        } else {
            foreach ($tables as $table) {
                $this->OptimizeTable($table);
            }

            return true;
        }
    }

    /**
     * optimizes a specified table
     * NOTE: This is MySQL specific.
     *
     * @param   string $table the database we're optimizing
     *
     * @return  bool
     */
    public function OptimizeTable($table)
    {
        $query = sprintf("OPTIMIZE TABLE %s", $table);

        return $this->RawQuery($query);
    }

    /**
     * checks whether a table exists or not
     *
     * @param   string $database the database to look in
     * @param   string $table    the table to look for
     *
     * @return  bool
     */
    public function TableExists($database, $table)
    {
        $tables = $this->ListTables($database);
        if (false === $tables) {
            return false;
        } else {
            if (in_array($table, $tables)) {
                return true;
            }

            return false;
        }

    }

    /**
     * checks to see if a field exists in the database
     *
     * @param    string $field the field we're looking for
     * @param    string $table the table we're looking into
     *
     * @return    bool
     */
    public function FieldExists($field, $table)
    {
        $fields = $this->ListFields($table);

        $count = count($fields);

        for ($x = 0; $x < $count; $x++) {
            if ($fields[$x]['Field'] == $field) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param        $table
     * @param        $fieldname
     * @param        $fieldvalue
     * @param string $extras
     *
     * @return bool
     */
    public function RecordExists($table, $fieldname, $fieldvalue, $extras = '')
    {
        if ($this->GetItemCount($table, $fieldname, $fieldvalue, $extras) > 0) {
            return true;
        }

        return false;

    }

    /**
     * function  to count the items in the table based on (optional) specific criteria
     *
     * @param        $table
     * @param string $field_name
     * @param string $id
     * @param string $extras
     *
     * @return mixed
     */
    public function GetItemCount($table, $field_name = '', $id = '', $extras = '')
    {

        $query = sprintf("SELECT count(*) AS count FROM %s", $this->EscapeString($table));

        if (((strlen($field_name) > 1) && (strlen($id) > 0)) || (strlen($extras) > 2)) {
            $query .= " WHERE ";
        }

        if ((strlen($field_name) > 1) && (strlen($id) > 0)) {
            $query .= sprintf(" %s='%s' ", $this->EscapeString($field_name), $this->EscapeString($id));
        }

        if (strlen($extras) > 2) {
            $query .= sprintf(' %s ', $this->EscapeString($extras));
        }

        $results = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        return $results['count'];
    }

    /**
     * imports a text file of SQL statements into the database
     * IMPORTANT! All SQL statements must be on their own line and CANNOT contain multiple lines
     * DOES NOT check for file existence before proceeding. This is your responsibility
     *
     * @TODO make a version of this which uses transactions
     *
     * @param   string $file  the name/ path of the file to import
     * @param   int    $sleep number of seconds to sleep between queries. Necessary when doing a huge import
     *
     * @return  string
     */
    public function SQLImport($file, $sleep = 3)
    {
        $lines = file($file);

        foreach ($lines as $line_num => $line) {
            $output .= "Line #<strong>{$line_num}</strong>: " . htmlspecialchars($line) . "<br>\n";
            $this->InsertData($line);
            sleep($sleep);
        }

        return $output;
    }

    /**
     * runs a simple SQL truncate query on a table to empty it
     *
     * @param   string $table the table to be truncated
     *
     * @return  bool    TRUE on success, FALSE otherwise
     */
    public function EmptyTable($table)
    {
        $query = sprintf("TRUNCATE TABLE %s", $table);
        $this->lastQuery = $query;

        return $this->RawExec($query);
    }

    /**
     * runs a simple SQL query on a table to drop it
     *
     * @param   string $table the name of the table to drop
     *
     * @return  bool    TRUE on success, FALSE otherwise
     */
    public function DropTable($table)
    {
        $query = sprintf("DROP TABLE IF EXISTS %s", $table);
        $this->lastQuery = $query;

        return $this->RawExec($query);
    }

    /**
     * loops through a superglobal array to sanitize variables
     *
     * @param   array  $expected  an array of expected key names
     * @param   string $tehGlobal indicator of which global we're working with
     *
     * @return  array   the $output variable is populated with sanitized values
     */
    public function SanitizeValues($expected, $tehGlobal)
    {

        switch ($tehGlobal) {
            case "GET" :
                $invars = $_GET;
                break;
            case "POST" :
                $invars = $_POST;
                break;
            case "SERVER" :
                $invars = $_SERVER;
                break;
            case "REQUEST" :
                $invars = $_REQUEST;
                break;
            case "ENV" :
                $invars = $_ENV;
                break;
            case "COOKIE" :
                $invars = $_COOKIE;
                break;
            case "FILES" :
                $invars = $_FILES;
                break;
            case "GLOBALS" :
                $invars = $GLOBALS;
                break;
            default :
                $invars = false;
        }

        if ($invars) {
            $output = array();
            foreach ($expected AS $key) {
                if (!empty($invars[$key])) {
                    $output[$key] = $this->EscapeString($invars[$key]);
                }
            }
        }

        return $output;
    }

    /**
     * gets details about a table
     * NOTE: This is MySQL specific.
     *
     * @param   string $table
     *
     * @return  array
     */
    public function DescribeTable($table)
    {
        $query = sprintf("DESCRIBE %s", $table);
        $this->lastQuery = $query;

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     * prints a purdy formatted table using information
     * gained from the DescribeTable method  (above)
     *
     * @param   array $data the data that comes from DescribeTable
     *
     * @return  string an HTML snippet
     */
    public function DumpDescribe($data)
    {
        $output = '';
        $output .= "<table>\n";
        $output .= "<thead>\n";
        $output .= "<tr>\n";
        $output .= "<th scope=\"col\">Field Name</th>\n";
        $output .= "<th scope=\"col\">Type</th>\n";
        $output .= "<th scope=\"col\">Null?</th>\n";
        $output .= "<th scope=\"col\">Key</th>\n";
        $output .= "<th scope=\"col\">Default</th>\n";
        $output .= "<th scope=\"col\">Extra</th>\n";
        $output .= "</tr>\n";
        $output .= "</thead>\n";
        $output .= "<tbody>\n";

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output .= "<tr>\n";
            $output .= "<th scope=\"row\">" . $data[$x]['Field'] . "</th>\n";
            $output .= "<td>" . $data[$x]['Type'] . "</td>\n";
            $output .= "<td>" . $data[$x]['Null'] . "</td>\n";
            $output .= "<td>" . $data[$x]['Key'] . "</td>\n";
            $output .= "<td>" . $data[$x]['Default'] . "</td>\n";
            $output .= "<td>" . $data[$x]['Extra'] . "</td>\n";
            $output .= "</tr>\n";
        }
        $output .= "</tbody>\n";
        $output .= "</table>\n";

        return $output;
    }

    /**
     * gets the table's status info
     *
     * @param   string $table
     * @param null     $key
     *
     * @return  array
     */
    public function getTableStatus($table, $key = null)
    {
        $query = sprintf("SHOW TABLE STATUS LIKE '%s'", $table);
        $result = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (is_null($key)) {
            return $result;
        } else {
            return $result[$key];
        }
    }

    /**
     * gets the next autoincrementing id from a table
     * NOTE: This is MySQL specific.
     *
     * @param   string $table
     *
     * @return  int
     */
    public function NextID($table)
    {
        return $this->getTableStatus($table, 'Auto_increment');
    }

    /**
     * submits multiple queries to the database. Primarily used for disparate queries.
     * Inserting multiple values for essentially the same query is the domain of prepared statements.
     * See InsertMultiple for a way to do that
     *
     * @param   array $array     the array of queries
     * @param   bool  $sleep     whether or not to sleep every so often (to reduce some load)
     * @param   int   $sleepNum  how many iterations to run before sleeping
     * @param   int   $sleepTime how long to sleep
     *
     * @return  bool
     */
    public function MultiQuery($array, $sleep = false, $sleepNum = null, $sleepTime = null)
    {
        try {
            $this->conn();

            $count = count($array);
            for ($x = 0; $x < $count; $x++) {

                $this->lastQuery = $array[$x];

                // if we're sleeping, lets figure out how long and when
                if (false !== $sleep) {
                    if ((!is_null($sleepNum)) && (!is_null($sleepTime))) {

                        // do it
                        if ($x % $sleepNum == 0) {
                            sleep($sleepTime);
                        }
                    }
                }

                if (false === $this->db->query($array[$x])) {
                    throw new PDOException();
                }
            }

            return true;
        } catch (PDOException $e) {
            echo $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }
    }

    /**
     * Like above: submits multiple queries to the database. Primarily used for disparate queries.
     * Inserting multiple values for essentially the same query is the domain of prepared statements.
     * See InsertMultiple for a way to do that
     * This version performs this work as a transaction
     *    NOTE: This method will return true, even for MyISAM tables (which do not support transactions)
     *
     * @TODO modify to check whether a transaction is already running first. See: http://www.php.net/manual/en/pdo.begintransaction.php#81022
     *
     * @param   array $queries the array of queries
     *
     * @return  bool
     */
    public function MultiQueryTransaction($queries)
    {
        $this->conn();

        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->db->beginTransaction();

            foreach ($queries AS $query) {
                $this->lastQuery = $query;
                $this->db->exec($query);
            }

            $this->db->commit();

        } catch (PDOException $e) {
            $this->db->rollBack();
            echo "Failed: " . $e->getMessage() . ' Query Was:' . $this->lastQuery;

            return false;
        }

        return true;
    }

    /**
     * gets an array of information about a database
     * NOTE: this is MySQL specific
     *
     * @param   string $database the database we're working with
     *
     * @return  array
     */
    public function GetDBInfo($database)
    {
        $query = sprintf("SHOW TABLE STATUS FROM %s;", $database);

        return $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);
    }

    /**
     * STATUS: TESTED
     *
     * @param   string $query
     *
     * @return  array
     */
    public function ExplainQuery($query)
    {
        $query2 = sprintf("EXPLAIN %s", $query);

        return $this->SelectSingleRecord($query2, PDO::FETCH_ASSOC);
    }

    /**
     * gets a random integer based on # of results. this can be used instead of RAND()
     * This is the preferred over RAND() because of its portability beyond MySQL
     *
     * @param   string   $table
     * @param   string   $field_name
     * @param int|string $id
     * @param string     $extras
     *
     * @return  int
     */
    public function makeRand($table, $field_name = '', $id = '', $extras = '')
    {
        $count = $this->GetItemCount($table, $field_name, $id, $extras);
        if (false === $count) {
            return false;
        }

        return mt_rand(0, $count - 1);
    }

    /**
     * retrieves the latest n records in the database
     *
     * @param   string $table
     * @param   string $orderField the field used to order the results
     * @param   string $orderDir   the direction to do the ordering.
     * @param   int    $limit      limit for the # of results returned
     * @param   string $fields     the fields to return
     * @param   string $extras     any optional extras to be included in the query for determining results
     *
     * @return  array
     */
    public function getLatestRecords($table, $orderField, $orderDir = 'DESC', $limit = 1, $fields = '*', $extras = null)
    {
        $query = sprintf("SELECT %s FROM %s", $this->EscapeString($fields), $this->EscapeString($table));

        if (!is_null($extras)) {
            $query .= sprintf(" %s ", $this->EscapeString($extras));
        }

        $query .= sprintf(" ORDER BY %s %s LIMIT %s", $this->EscapeString($orderField), $this->EscapeString($orderDir), $this->EscapeString($limit));

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     * retrieves the latest single record in the database
     *
     * @param   string $table
     * @param   string $orderField the field used to order the results
     * @param   string $orderDir   the direction to do the ordering.
     * @param   string $fields     the fields to return
     * @param   string $extras     any optional extras to be included in the query for determining results
     *
     * @return  array
     */
    public function getLatestSingleRecord($table, $orderField, $orderDir = 'DESC', $fields = '*', $extras = null)
    {
        $results = $this->getLatestRecords($table, $orderField, $orderDir, 1, $fields, $extras);
        if (false === $results) {
            return false;
        }

        return $results[0];
    }

    /**
     * Gets the primary key ID from a key. Used when providing a key that isn't the PKID
     *
     * @param   string $table
     * @param   string $idField
     * @param   string $keyField
     * @param   string $keyVal
     *
     * @return  string
     */
    public function getIDFromKey($table, $idField, $keyField, $keyVal)
    {
        $query = sprintf("SELECT %s FROM %s WHERE %s='%s' LIMIT 1", $this->EscapeString($idField), $this->EscapeString($table), $this->EscapeString($keyField), $this->EscapeString($keyVal));
        $data = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);
        if (false === $data) {
            return false;
        } else {
            return $data[$idField];
        }
    }

    /**
     *  Utility method used by the 'INClause' method
     *
     * @param array $array
     *
     * @return string
     */
    private function makeSQLIN($array)
    {
        // sanity check. Should not be needed
        // because INClause takes care of this
        if (!is_array($array)) {
            return "'" . $array . "'";
        }

        // loop through each item adding single quotes around each
        foreach ($array AS $item) {
            $output[] = "'" . $item . "'";
        }

        $output = implode(", ", $output);

        return $output;
    }

    /**
     * Creates a SQL 'IN' Clause
     *
     * @param   string $field  the field part of the clause
     * @param   array  $ids    the IDs for the IN clause
     * @param   bool   $escape whether or not to escape the values
     *
     * @return  string
     */
    public function INClause($field, $ids, $escape = true)
    {
        if (true === $escape) {
            $ids = $this->EscapeString($ids);
        }

        if (is_array($ids)) {
            $output = " $field IN (" . $this->makeSQLIN($ids) . ") ";
        } else {
            $output = " $field='$ids' ";
        }

        return $output;
    }

    /**
     * Generates a random string for use as a key
     *
     * @param   string $table
     * @param   string $field
     * @param   int    $length
     *
     * @return  string
     */
    public function generateKey($table, $field, $length = 8)
    {
        $key = Strings::RandomString($length);
        // prevent duplicates by appending a number at the end of any key that's the same as this one
        // this should be extremely rare
        $num = 1;
        while ($this->RecordExists($table, $field, $key)) {
            $num++;
            // if previous name existed then try another key with $num
            $key = $key . "-" . $num;
        }

        return $key;
    }

    /**
     * gets the primary key of a table
     * NOTE: this method is MySQL specific
     *
     * @param   string $table the table we're working with
     *
     * @return string
     */
    public function getPrimaryKey($table)
    {
        $query = sprintf("SHOW KEYS FROM %s", $table);
        $results = $this->SelectData($query, PDO::FETCH_ASSOC);

        $count = count($results);

        for ($x = 0; $x < $count; $x++) {
            if ($results[$x]['Key_name'] == 'PRIMARY') {
                return $results[$x]['Column_name'];
            }
        }

        return false;
    }


    /**
     *  Loops through an array of options to build a query with them as
     *  " $field='$item1' OR $field='$item2'" etc. This is preferred over the
     *  SQL 'IN' syntax mostly because this method can be extended in sub-classes
     *
     * @param   string $field
     * @param   array  $array
     *
     * @return  array
     */
    public static function makeQueryOrLoop($field, $array)
    {
        $output = '';

        if (!is_array($array)) {
            return false;
        }

        $count = count($array);
        $i = $count;
        for ($x = 0; $x < $count; $x++) {
            $output .= " $field='" . $array[$x] . "'";
            if ($i > 1) {
                $output .= " OR ";
                $i--;
            }
        }

        return $output = '';
    }

    /**
     * Runs mysql-specific SHOW CREATE TABLE query and shoots back the result
     *
     * @param $table
     *
     * @return bool|string
     */
    public function showCreateTable($table)
    {
        $data = $this->RawQuery('SHOW CREATE TABLE ' . $table);
        if (false === $data) {
            return false;
        }

        return $data[0][1];

    }

    /**
     * Runs mysql-specific SHOW CREATE DATABASE query and shoots back the result
     *
     * @param $database
     *
     * @return bool|string
     */
    public function showCreateDatabase($database)
    {
        $data = $this->RawQuery('SHOW CREATE DATABASE ' . $database);
        if (false === $data) {
            return false;
        }

        return $data[0][1];

    }


    /**
     * @param        $table
     * @param string $fields
     * @param null   $groupBy
     *
     * @return array|bool
     */
    public function findDuplicates($table, $fields = '*', $groupBy = null)
    {

        if (is_null($groupBy)) {
            $groupBy = $fields;
        }

        $query = sprintf('SELECT %s FROM %s GROUP BY %s HAVING COUNT(*)>1',
            $this->EscapeString($fields), $this->EscapeString($table), $this->EscapeString($groupBy));

        return $this->RawQuery($query);
    }


    /**
     * @param $table
     * @param $field
     *
     * @return array|bool
     */
    public function getEnumDefaults($table, $field)
    {
        $query = sprintf("SHOW FIELDS FROM %s WHERE Field = '%s'", $this->EscapeString($table), $this->EscapeString($field));

        $data = $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        $type = $data['Type'];
        $data = null;

        $eliminate = array('enum(', "'", ')');

        foreach ($eliminate AS $e) {
            $type = trim(str_replace($e, '', $type));
        }

        return explode(',', $type);
    }


    /**
     * Returns a useful string to be used in SQL queries for finding the domain
     * portion from a URL
     *
     * @param  string $field
     *
     * @return string
     */
    public function urlDomainString($field)
    {
        return sprintf(" substring_index(substring_index(substring_index(%s, '/', 3), '/', -1), '.', -2) ", $this->EscapeString($field));
    }

    /**
     * @param        $query
     * @param        $file
     * @param string $mode
     * @param string $delimiter
     * @param string $enclosure
     *
     * @return bool
     */
    public function dataToCSV($query, $file, $mode = 'w', $delimiter = ',', $enclosure = '"')
    {
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        $fp = fopen($file, $mode);
        if (false === $fp) {
            return false;
        }

        //get the field names
        $keys = array_keys($data[0]);

        //put the field names up front in the array
        array_unshift($data, $keys);

        foreach ($data as $fields) {
            fputcsv($fp, $fields, $delimiter, $enclosure);
        }

        fclose($fp);

        return true;
    }
}