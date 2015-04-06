<?php

/**
 *
 * Extends the db class to perform basic database interaction.  Since 99% of
 * all the work I do is creating the same CRUD stuff over and over, it became
 * very apparent that what I needed was a simple class to do all these things
 * rather than writing the same fucking code over and over and over and over
 *
 */
class basicCRUDPDO extends dbPDO
{

    /**
     * @param   string $dbName     database name
     * @param   string $user       database username
     * @param   string $pass       database password
     * @param   array  $opts       connection options.
     * @param   string $dbType     type of database - must be 'mysql', 'sqlite', or 'pgsql'
     * @param   string $hostORpath ip or name of host (in the case of mysql or pgsql) or file path to db (in case of sqlite)
     * @param   string $port
     */
    public function __construct($dbName, $user, $pass, $opts = null, $dbType = 'mysql', $hostORpath = 'localhost', $port = '')
    {
        parent::__construct($dbName, $user, $pass, $opts, $dbType, $hostORpath, $port);
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
     * Inserts a single record in the database using an associative array of values
     *
     * @param   array  $data        array of key=>value pairs for insertion
     * @param   string $table       the table to insert into
     * @param   bool   $checkFields (optional) whether or not to check for each fields' existence
     * @param   bool   $checkTable  (optional) whether or not to check for the table's existence
     *
     * @return  bool    true on success
     */
    public function insert($data, $table, $checkFields = false, $checkTable = false)
    {
        // (optional) check to make sure the table actually exists
        if (true === $checkTable) {
            if (false === $this->TableExists($this->dbName, $table)) {
                return false;
            }
        }

        // (optional) check to make sure each field actually exists
        // populate $fData (final data) with the right stuff
        if (true === $checkFields) {

            $tCols = $this->ListFields($table);
            $count = count($tCols);

            for ($x = 0; $x < $count; $x++) {
                $cols[] = $tCols[$x]['Field'];
            }

            foreach ($data AS $key => $val) {
                if (in_array($key, $cols)) {
                    $fData[$key] = $val;
                }
            }
        } // otherwise $fData (final data) is the same data, untouched
        else {
            $fData = $data;
        }

        return $this->InsertArray($fData, $table);
    }

    /**
     * Updates a record in the database using an associative array of values
     *
     * @param   array  $data          data for the updated record
     * @param   string $table         table
     * @param   string $criteriaField field (such as the PK field)
     * @param   string $criteriaValue value for the criteria field (again, such as the PK)
     * @param   string $extras        (optional) any extra stuff for the query
     * @param   bool   $checkTable    (optional) whether or not to check that the table exists first
     * @param   bool   $checkFields   (optional) whether or not to check that each field exists first
     *
     * @return  bool
     */
    public function update($data, $table, $criteriaField, $criteriaValue, $extras = null, $checkTable = false, $checkFields = false)
    {
        // (optional) check to make sure the table actually exists
        if (true === $checkTable) {
            if (false === $this->TableExists($this->dbName, $table)) {
                return false;
            }
        }

        // (optional) check to make sure each field actually exists
        // populate $fData (final data) with the right stuff
        if (true === $checkFields) {
            $tCols = $this->ListFields($table);
            $count = count($tCols);

            for ($x = 0; $x < $count; $x++) {
                $cols[] = $tCols[$x]['Field'];
            }

            foreach ($data AS $key => $val) {
                if (in_array($key, $cols)) {
                    $fData[$key] = $val;
                }
            }
        } // otherwise $fData (final data) is the same data, untouched
        else {
            $fData = $data;
        }

        return $this->UpdateArray($table, $fData, $criteriaField, $criteriaValue, $extras);
    }

    /**
     * Deletes a record. Can include some other criteria for the deletion(s)
     *
     * @param   string $table         table from which we're deleting records
     * @param   string $criteriaField the criteria field (such as the PK field)
     * @param   string $criteriaValue the criteria value (such as the PK value)
     * @param   string $extras        (optional) any extra stuff for the query
     * @param   bool   $checkTable    (optional) whether or not to check that the table exists first
     * @param   bool   $checkField    (optional) whether or not to check that the criteria field exists first
     *
     * @return  bool
     */
    public function delete($table, $criteriaField, $criteriaValue, $extras = null, $checkTable = false, $checkField = false)
    {
        $query = '';

        // (optional) check to make sure the table actually exists
        if (true === $checkTable) {
            if (false === $this->TableExists($this->dbName, $table)) {
                return false;
            }
        }

        // (optional) check to make sure the criteriaField actually exists
        if (true === $checkField) {
            $tCols = $this->ListFields($table);
            $count = count($tCols);

            for ($x = 0; $x < $count; $x++) {
                $cols[] = $tCols[$x]['Field'];
            }

            if (!in_array($criteriaField, $cols)) {
                return false;
            }
        }

        // put together the query
        $query .= sprintf("DELETE FROM %s WHERE %s='%s'", $this->EscapeString($table), $this->EscapeString($criteriaField), $this->EscapeString($criteriaValue));

        if (!is_null($extras)) {
            $query .= sprintf(" %s ", $this->EscapeString($extras));
        }

        return $this->DeleteData($query);
    }

    /**
     * This selects a SINGLE record from the database based on specific criteria (such as a unique ID)
     *
     * @param   string $table      the table to retrieve from
     * @param   string $idField    the field which holds a unique ID
     * @param   string $id         the unique ID value
     * @param   string $fields     the fields to retrieve in the query
     * @param   bool   $checkTable (optional) whether or not to check that the table exists first
     * @param   bool   $checkField (optional) whether or not to check that the ID field exists
     *
     * @return  array
     */
    public function getByID($table, $idField, $id, $fields = ' * ', $checkTable = false, $checkField = false)
    {
        // (optional) check to make sure the table actually exists
        if (true === $checkTable) {
            if (false === $this->TableExists($this->dbName, $table)) {
                return false;
            }
        }

        // (optional) check to make sure the criteriaField actually exists
        if (true === $checkField) {
            $tCols = $this->ListFields($table);
            $count = count($tCols);

            for ($x = 0; $x < $count; $x++) {
                $cols[] = $tCols[$x]['Field'];
            }

            if (!in_array($idField, $cols)) {
                return false;
            }
        }

        // put together the query
        $query = sprintf("SELECT %s FROM %s WHERE %s='%s'",
            $this->EscapeString($fields),
            $this->EscapeString($table),
            $this->EscapeString($idField),
            $this->EscapeString($id));

        return $this->SelectSingleRecord($query, PDO::FETCH_ASSOC);

    }

    /**
     * Selects all records from a table. Can include a number of optional criteria
     *
     * @param   string $table         the table to retrieve from
     * @param   string $fields        the fields to retrieve
     * @param   string $criteriaField (optional) any specific fields to base the query on
     * @param   string $criteriaValue (optional) the value for the criteria field
     * @param   string $extras        (optional) extra stuff in the query, such as extra criteria
     * @param   string $orderBy       (optional) column to sort by
     * @param   string $orderDir      (optional) direction of the sort
     * @param   int    $limit         (optional) limit to the # of returned results
     * @param   bool   $checkTable    (optional) whether to check that the table exists
     * @param   bool   $checkField    (optional) whether to check that the criteria field exists
     *
     * @return  array
     */
    public function getAll($table, $fields = ' * ', $criteriaField = null, $criteriaValue = null, $extras = null, $orderBy = null, $orderDir = '', $limit = null, $checkTable = false, $checkField = false)
    {
        $query = '';

        // (optional) check to make sure the table actually exists
        if (true === $checkTable) {
            if (false === $this->TableExists($this->dbName, $table)) {
                return false;
            }
        }

        // (optional) check to make sure the criteriaField actually exists
        if ((true == $checkField) && (!is_null($criteriaField))) {
            $tCols = $this->ListFields($table);
            $count = count($tCols);

            for ($x = 0; $x < $count; $x++) {
                $cols[] = $tCols[$x]['Field'];
            }

            if (!in_array($criteriaField, $cols)) {
                return false;
            }
        }

        $query .= sprintf(" SELECT %s FROM %s ", $this->EscapeString($fields), $this->EscapeString($table));

        if ((!is_null($criteriaField)) && (!is_null($criteriaValue))) {
            $query .= sprintf(" WHERE %s='%s' ", $this->EscapeString($criteriaField), $this->EscapeString($criteriaValue));
        }

        if (!is_null($extras)) {
            $query .= sprintf(" %s ", $this->EscapeString($extras));
        }

        if (!is_null($orderBy)) {
            $query .= sprintf(" ORDER BY %s %s ", $this->EscapeString($orderBy), $this->EscapeString($orderDir));
        }

        if (!is_null($limit)) {
            $query .= sprintf(" LIMIT %s ", $this->EscapeString($limit));
        }

        return $this->SelectData($query, PDO::FETCH_ASSOC);
    }

    /**
     * Gets a count of all items based on criteria
     *
     * @param   string $table  the table to get a count from
     * @param   string $extras any optional extra criteria for selecting records
     *
     * @return  int
     */
    public function countItems($table, $extras = null)
    {
        $query = sprintf("SELECT count(*) AS count FROM %s", $this->EscapeString($table));

        if (!is_null($extras)) {
            $query .= $extras;
        }

        $data = $this->SelectData($query, PDO::FETCH_NUM);

        return $data[0]['count'];
    }

    /**
     * Takes a SQL query and turns it into an HTML table
     *
     * @param   string $query the query to select the data
     * @param   array  $attrs (optional) any attributes for the table
     *
     * @return  string
     */
    public function SQL2HTMLTable($query, $attrs = null)
    {
        $output = '';

        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        }

        $output .= "<table ";

        if (!is_null($attrs)) {
            foreach ($attrs AS $key => $val) {
                $output .= " $key=\"$val\" ";
            }
        }

        $output .= ">";

        $keys = array_keys($data[0]);

        $output .= "<thead>";
        $output .= "<tr>";

        foreach ($keys AS $val) {
            $output .= "<th scope=\"col\">$val</th>";
        }

        $output .= "</tr>";
        $output .= "</thead>";
        $output .= "<tbody>";

        $count = count($data);
        for ($x = 0; $x < $count; $x++) {
            $output .= "<tr>";

            foreach ($data[$x] AS $val) {
                $output .= "<td>$val</td>";
            }

            $output .= "</tr>";
        }

        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
    }

}