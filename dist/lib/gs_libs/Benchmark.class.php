<?php

/**
 *
 * provides a way to perform timings of how long things take
 *
 * USAGE EXAMPLE
 *
 * $T = new benchmark();
 *
 * $T->start();
 * // ... do whatever that must be timed ...
 * $T->stop();
 * echo "Load time took: " . $T->display() . "s";
 *
 */
class Benchmark
{

    public $start_time, $end_time, $execution_time;
    private $connection, $db;

    /**
     *
     * @param   bool  $start      whether or not to atomatically start the timer when the object is created
     * @param   array $connection database connection details
     */
    public function __construct($start = true, $connection = null)
    {
        $this->connection = $connection;
        if (true === $start) {
            $this->start();
        }
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
    }

    /**
     *
     */
    public function start()
    {
        $this->start_time = microtime(true);
    }

    /**
     *
     */
    public function stop()
    {
        $this->end_time = microtime(true);
        $this->execution_time = ($this->end_time - $this->start_time);
    }

    /**
     *
     * @param   int $decimal_place
     *
     * @return  string
     */
    public function display($decimal_place = 8)
    {
        return number_format($this->execution_time, $decimal_place);
    }

    /**
     * Logs the benchmark information to database
     *
     * @param    string $table
     * @param    string $startField
     * @param    string $endField
     * @param    string $totalField
     * @param    string $dateField
     *
     * @return    bool
     */
    public function logtoDB($table, $startField = null, $endField = null, $totalField = null, $dateField = null)
    {
        $fields = array();

        if (!is_array($this->connection)) {
            return false;
        }

        $this->db = new dbPDO($this->connection['dbName'], $this->connection['user'], $this->connection['pass'], $this->connection['opts']);

        if (false === $this->db) {
            return false;
        }

        if (!is_null($startField)) {
            $fields[$startField] = $this->start_time;
        }
        if (!is_null($endField)) {
            $fields[$endField] = $this->end_time;
        }
        if (!is_null($totalField)) {
            $fields[$totalField] = $this->execution_time;
        }
        if (!is_null($dateField)) {
            $fields[$dateField] = Date::UTC2MySQLDateTime();
        }

        return $this->db->InsertArray($fields, $table);
    }

}
