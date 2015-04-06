<?php

/**
 * Custom exception handler.  Extends PHP Exception and adds a few new methods
 */
class gException extends Exception
{
    public $ExceptionQuery;

    /**
     * constructor. Operates exactly like parent constructor except explicitly sets $previous
     *
     * @param   string    $message  the custom message you define
     * @param   int       $code     the custom code you define
     * @param   Exception $previous the previous exception thrown
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct($message, (int)$code, $previous);
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
     * Assembles a message with more data than the default
     *
     * @param   bool   $toString whether or not to return a string or an array
     * @param   string $delim    delimiter to use, if returning a string
     *
     * @return  string|array
     */
    public function gMessage($toString = true, $delim = PHP_EOL)
    {
        $message['time'] = date('Y-m-d G:i:s');
        $message['text'] = self::getmessage();
        $message['code'] = self::getCode();
        $message['file'] = self::getFile();
        $message['line'] = self::getLine();
        $message['trace'] = self::getTraceAsString();
        $message['fullURL'] = Network::getFullSelf();

        if (false === $toString) {
            return $message;
        } else {
            $output = '';
            foreach ($message AS $key => $val) {
                $output .= $key . ': ' . $val . $delim;
            }

            return $output;
        }

    }

    /**
     * sends an email with exception information
     *
     * @param   string $to   recipient of the email message
     * @param   string $from sender of the email message
     *
     * @return  bool
     */
    public function sendExceptionEmail($to, $from)
    {
        return gFormUtils::gFormEmail($to, $from, 'Exception Thrown on ' . Network::getThisDomain(), self::gMessage(true));
    }

    /**
     * logs exception information to database
     *
     * @param   array  $connection database connection details
     * @param   string $table      the table into which the info will be inserted
     *
     * @return  bool
     */
    public function logToDatabase($connection, $table)
    {
        $db = new dbPDO($connection['dbName'], $connection['user'], $connection['pass']);

        $data = self::gMessage(false);

        $fields = implode(',', array_keys($data));

        foreach ($data AS $val) {
            $values[] = "'" . $db->EscapeString($val) . "'";
        }
        $values = implode(',', $values);

        $query = sprintf("INSERT INTO '%s' ($fields) VALUES($values)", $db->EscapeString($table));

        $this->ExceptionQuery = $query;

        return $db->InsertData($query);
    }

    /**
     * logs the exception to a file
     *
     * @param   string $pathToFile full filesystem path to the file the log will be added to
     * @param   string $delimChar  the character that will delimit new lines
     * @param   string $mode
     *
     * @return  bool    TRUE on success
     */
    public function logToFile($pathToFile, $delimChar = '-', $mode = 'a+')
    {
        $delimiter = str_pad('', 70, $delimChar);

        $handle = fopen($pathToFile, $mode);
        if (!$handle) {
            return false;
        } else {
            if (flock($handle, LOCK_EX)) {
                fwrite($handle, self::gMessage(true) . $delimiter . PHP_EOL);
                flock($handle, LOCK_UN);
                fclose($handle);

                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Simple function to print the message
     * return void Message is echoed to screen
     */
    public function printMessage()
    {
        echo self::gMessage(true);
    }
}
