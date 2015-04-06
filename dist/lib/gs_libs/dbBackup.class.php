<?php

/**
 * Class dbBackup
 */
class dbBackup extends dbPDO
{

    /**
     * @param        $dbName
     * @param        $user
     * @param        $pass
     * @param null   $opts
     * @param string $dbType
     * @param string $hostORpath
     * @param string $port
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
     * Sets a path into which the backup files will be placed
     *
     * @param   string $path filesystem path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Sets a string that will be prepended onto the file string
     */
    public function setPreString($str = '')
    {
        $this->preString = $str;
    }

    /**
     * Sets a string that will be prepended onto the file string
     */
    public function setPostString($str = '')
    {
        $this->postString = $str;
    }


    /**
     * @param      $table
     * @param null $name
     */
    public function backupTable($table, $name = null)
    {
        $return = '';

        if (is_null($name)) {
            $name = $table;
        }

        $return .= PHP_EOL . 'DROP TABLE ' . $table . ';' . PHP_EOL;

        $return .= PHP_EOL . $this->showCreateTable($table) . ';' . PHP_EOL;

        $data = $this->getData($table);
        if (false === $data) {
            $this->writeFile($this->preString . $name . $this->postString . '.sql', $return);
        } else {

            $dbFields = $this->tableColumns($table);

            $numFields = count($dbFields);

            $pre = PHP_EOL . 'INSERT INTO ' . $table . ' (' . implode(',', $dbFields) . ') VALUES(';
            $post = ');' . PHP_EOL;

            $count = count($data);

            for ($x = 0; $x < $count; $x++) {
                $return .= $pre;

                for ($i = 0; $i < $numFields; $i++) {

                    $return .= '"' . $this->EscapeString($data[$x][$i]) . '"';

                    if ($i < ($numFields - 1)) {
                        $return .= ',';
                    }

                }
                $return .= $post;
                $data[$x] = null;
            }
        }

        $data = null;
        $this->writeFile($this->preString . $name . $this->postString . '.sql', $return);
        $return = null;
    }


    /**
     * @param null $array
     *
     * @return bool
     */
    public function dumpMultipleTables($array = null)
    {
        $tables = $this->ListTables($this->dbName);
        if (false === $tables) {
            return false;
        }
        if (!is_array($tables)) {
            return false;
        }

        foreach ($tables AS $t) {
            if (is_array($array)) {
                if (in_array($t, $array)) {
                    $this->backupTable($t, $t);
                }
            } else {
                $this->backupTable($t, $t);
            }
        }

        return true;
    }

    /**
     * Gets an array of table columns
     *
     * @param $table
     *
     * @return array|bool
     */
    public function tableColumns($table)
    {
        $fields = $this->SelectSingleRecord('SELECT * FROM ' . $table . ' LIMIT 1 ', PDO::FETCH_ASSOC);
        if (false === $fields) {
            return false;
        }

        return array_keys($fields);
    }


    /**
     * @param $table
     *
     * @return bool|array
     */
    public function getData($table)
    {
        $data = $this->SelectData('SELECT * FROM ' . $table, PDO::FETCH_NUM);
        if (false === $data) {
            return false;
        }

        return $data;
    }

    /**
     * @param $file
     * @param $data
     *
     * @return bool
     */
    public function writeFile($file, $data)
    {
        $file = $this->path . $file;
        $handle = fopen($file, 'w+');

        if (!$handle) {
            return false;
        } else {
            if (flock($handle, LOCK_EX)) {
                fwrite($handle, $data);
                flock($handle, LOCK_UN);
                // release the lock
                fclose($handle);

                return true;
            }

            return false;
        }
    }
}
