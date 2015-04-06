<?php

/**
 *
 * connection-driven session handling
 *
 */
class Sessions extends dbPDO
{

    private $connection;
    private $maxLifetime;

    /**
     *
     * class constructor
     *
     * @param   array  $connection
     * @param   int    $maxLifetime how long sessions should last
     * @param   string $timezone    what timezone to use
     */
    public function __construct($connection, $maxLifetime = 6000, $timezone = 'UTC')
    {
        parent::__construct($connection['dbName'], $connection['user'], $connection['pass'], $connection['opts'], $connection['dbType'], $connection['hostORpath'], $connection['port']);
        $this->connection = $connection;
        $this->maxLifetime = $maxLifetime;

        session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc'));

        session_start();

        date_default_timezone_set($timezone);

        // this runs the garbage collection every time the class is created
        $this->gc();

        register_shutdown_function('session_write_close');

    }

    /**
     *
     * class destructor
     */
    public function __destruct()
    {
        // this runs the garbage collection every time the class is destroyed
        $this->gc();

        session_write_close();
        parent::__destruct();
    }

    /**
     *
     * call this to reset the session ID
     * resets the session ID
     */
    public function resetSessionID($deleteOld = false)
    {
        $old = session_id();
        session_regenerate_id($deleteOld);
        $new = session_id();

        return $this->regenerate_id($old, $new);
    }

    /**
     * regenerates the session ID
     *
     * @param $old
     * @param $new
     *
     * @return bool
     */
    private function regenerate_id($old, $new)
    {

        $query = sprintf("UPDATE sessions SET sessionID = '%s', sessionTouched='" . time() . "' WHERE sessionID = '%s'", $this->EscapeString($new), $this->EscapeString($old));

        //echo $query;
        return $this->UpdateData($query);

    }

    /**
     *
     * opens a session
     *
     * @param    $sess_path
     * @param    $sess_name
     *
     * @return    bool
     */
    public function open($sess_path, $sess_name)
    {
        //echo "open<br>";
        return true;
    }

    /**
     *
     * closes a session
     *
     * @return    bool
     */
    public function close()
    {
        //echo "sess_close<br>";
        return true;
    }

    /**
     *
     * reads session data. Also updates the last time the session was touched
     *
     * @param    int $id
     *
     * @return    string
     */
    public function read($id)
    {
        //echo "sess_read<br>";
        $query = sprintf("SELECT sessionData FROM sessions WHERE sessionID = '%s' LIMIT 1", $this->EscapeString($id));

        //echo "$query <br>";
        $data = $this->SelectData($query, PDO::FETCH_ASSOC);

        if (false === $data) {
            return false;
        } else {

            $query = sprintf("UPDATE sessions SET sessionTouched = '" . time() . "' WHERE sessionID = '%s'", $this->EscapeString($id));

            //echo $query . "<br>";
            //echo $data . "<br>";

            $this->UpdateData($query);

            return $data[0]['sessionData'];
        }
    }

    /**
     *
     * writes information to the session record
     *
     * @param    int    $id
     * @param    string $data
     *
     * @return    bool
     */
    public function write($id, $data)
    {

        //echo "write<br>";
        $query = sprintf("REPLACE INTO sessions (sessionID, sessionData, sessionTouched) VALUES('%s', '%s', '" . time() . "')", $this->EscapeString($id), $this->EscapeString($data));
        //echo $query . "<br>";

        $this->UpdateData($query);

        return true;
    }

    /**
     *
     * destroys the session
     *
     * @param    int $id
     *
     * @return    bool
     */
    public function destroy($id)
    {

        //echo "destroy<br>";
        $query = sprintf("DELETE FROM sessions WHERE sessionID = '%s'", $this->EscapeString($id));
        //echo $query . "<br>";
        $this->DeleteData($query);

        return $this->gc();
    }

    /**
     *
     * Session garbage collection dumps expired sessions
     *
     * @return    bool
     */
    public function gc()
    {

        $old = time() - $this->maxLifetime;

        //echo "gc<br>";
        $query = sprintf("DELETE FROM sessions WHERE sessionTouched < %s ", $this->EscapeString($old));

        //echo $query . "<br>";
        return $this->DeleteData($query);
    }

    /**
     *
     * sets multiple session variables at the same time.
     *
     * @param  array $array
     *
     * @return  bool
     */
    public function setSessionVars($array)
    {
        if (is_array($array)) {
            foreach ($array AS $key => $val) {
                $_SESSION[$key] = $val;
            }

            return true;
        }

        return false;
    }

}