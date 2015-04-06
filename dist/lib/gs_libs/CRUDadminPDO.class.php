<?php

/**
 *
 * This class uses the basicCRUD class to handle the vast majority of the
 * administration of database entries. This class will provide coverage of
 * the majority of entry administration tasks and can then be extended as
 * needed for more specific cases.
 */
class CRUDadminPDO extends basicCRUDPDO
{

    public $dbTable, $PKfield;

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
     *
     * @param   array $array a list of configuration options
     */
    public function config($array)
    {
        foreach ($array AS $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     *
     * Adds a new record to the database
     *
     * @param   array $data associative array of data to be inserted
     *
     * @return  bool
     */
    public function insert($data)
    {
        return parent::insert($data, $this->dbTable, true);
    }

    /**
     *
     * Gets the latest inserted record (based on PKID) in the table
     *
     * @return    string
     */
    public function lastInsertID()
    {
        $query = sprintf("SELECT %s from %s ORDER BY %s DESC LIMIT 1", $this->PKfield, $this->dbTable, $this->PKfield);
        $data = parent::RawQuery($query);
        if (false === $data) {
            return false;
        }

        return $data[0][$this->PKfield];
    }

    /**
     *
     * Updates a database record
     *
     * @param   array $data associative array of data to be updated
     * @param   int   $id   primary key ID of the record to be updated
     *
     * @return  bool
     */
    public function update($data, $id)
    {
        return parent::update($data, $this->dbTable, $this->PKfield, $id, null, false, true);
    }

    /**
     *
     * Marks a record as deleted
     *
     * @param   int $id primary key ID of the record to be marked as deleted
     *
     * @return  bool
     */
    public function delete($id)
    {
        $data = array('deleted' => '1');

        return parent::update($data, $this->dbTable, $this->PKfield, $id);
    }

    /**
     *
     * Reinstates/ undeletes a record that was previously marked as deleted
     *
     * @param   int $id primary key ID of the record to be reinstated
     *
     * @return  bool
     */
    public function unDelete($id)
    {
        $data = array('deleted' => '0');

        return parent::update($data, $this->dbTable, $this->PKfield, $id);
    }

    /**
     *
     * Removes a record from the database
     *
     * @param   int $id primary key ID of the record to be deleted
     *
     * @return  bool
     */
    public function realDelete($id)
    {
        return parent::delete($this->dbTable, $this->PKfield, $id);
    }

    /**
     *
     * Permanently removes all records from the database which
     * have been marked as 'deleted'
     */
    public function purgeDeleted()
    {
        $query = sprintf("DELETE FROM %s WHERE deleted='1'", $this->EscapeString($this->dbTable));

        return parent::DeleteData($query);
    }

    /**
     *
     * Retrieves a record based on PKID
     *
     * @param   int    $id     primary key ID of the record to be retrieved
     * @param   string $fields database fields to be retrieved in the query
     *
     * @return  array
     */
    public function getByID($id, $fields = ' * ')
    {
        return parent::getByID($this->dbTable, $this->PKfield, $id, $fields);
    }

    /**
     *
     * Gets all records (subject to optional criteria)
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
    public function getAll($fields = ' * ', $criteriaField = 'deleted', $criteriaValue = '0', $extras = null, $orderBy = null, $orderDir = 'ASC', $limit = null)
    {

        if (is_null($orderBy)) {
            $orderBy = $this->PKfield;
        }

        return parent::getAll($this->dbTable, $fields, $criteriaField, $criteriaValue, $extras, $orderBy, $orderDir, $limit);
    }

    /**
     * Selects a specific record from the database
     *
     * @param   string $criteriaField the specific field used for the criteria to select the record
     * @param   string $criteriaValue the specific value for the field to select the record
     * @param   string $fields        the fields to retrieve from the query
     * @param   string $extras        any extra stuff for the query
     *
     * @return  array
     */
    public function SelectSpecificRecord($criteriaField, $criteriaValue, $fields = '*', $extras = null)
    {
        return parent::SelectSpecificRecord($this->dbTable, $criteriaField, $criteriaValue, $fields, $extras);
    }

    /**
     *
     * Generates a table to display all records (subject to optional criteria)
     *
     * @todo modify this to be more universally usable
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
    public function adminTable($fields = ' * ', $criteriaField = 'deleted', $criteriaValue = '0', $extras = null, $orderBy = null, $orderDir = 'ASC', $limit = null)
    {
        $content = '';

        if (is_null($orderBy)) {
            $orderBy = $this->PKfield;
        }

        $data = $this->getAll($fields, $criteriaField, $criteriaValue, $extras, $orderBy, $orderDir, $limit);

        if (false === $data) {
            return "<p class=\"warn\">There are no records in the database</p>\n";
        } else {

            $table = new adminTable();
            $table->open("class=\"data_table\"");
            $table->extContent('<caption>' . count($data) . Strings::strPad(_('Total Results')) . '</caption>');
            $table->extContent("<thead>");
            $table->startRow();
            $table->addHeader("ID", "col");
            $table->addHeader("Question", "col");
            $table->addHeader("Action", "col", " colspan=\"3\" ");
            $table->endRow();
            $table->extContent("</thead>\n<tbody>");

            $count = count($data);
            for ($x = 0; $x < $count; $x++) {

                $rowAttrs = array('class' => 'row_th');
                $actionCellRowclassAttr = " class=\"action_cell\" ";

                $table->startRow();
                $table->addHeader($data[$x][$this->PKfield], "row", $rowAttrs);
                $table->addCell($data[$x]['challengeQuestion']);

                $viewLink = "<a href=\"view.php?id=" . $data[$x][$this->PKfield] . "\">View</a>\n";
                $editLink = "<a href=\"edit.php?id=" . $data[$x][$this->PKfield] . "\">Edit</a>\n";

                if ($data[$x]['deleted'] == '1') {
                    $deleteLink = 'Deleted';
                } else {
                    $deleteLink = "<a href=\"delete.php?id=" . $data[$x][$this->PKfield] . "\">Delete</a>\n";
                }

                $table->addCell($viewLink, $actionCellRowclassAttr);
                $table->addCell($editLink, $actionCellRowclassAttr);
                $table->addCell($deleteLink, $actionCellRowclassAttr);

                $table->endRow();
            }
            $table->extContent("</tbody>");
            $table->close();
            $content .= $table->getAdminTable();
        }

        $content .= "<button class=\"navButton\" value=\"add.php\">Add A New Question...</button>";

        return $content;
    }

}
