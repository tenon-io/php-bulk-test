<?php

/**
 * Class logToConsole
 */
class logToConsole
{

    /**
     * @param        $var
     * @param string $name
     * @param bool   $now
     *
     * @return bool|void
     */
    public static function logVar($var, $name = '', $now = false)
    {
        if ($var === null) {
            $type = 'NULL';
        } else if (is_bool($var)) {
            $type = 'BOOL';
        } else if (is_string($var)) {
            $type = 'STRING[' . strlen($var) . ']';
        } else if (is_int($var)) {
            $type = 'INT';
        } else if (is_float($var)) {
            $type = 'FLOAT';
        } else if (is_array($var)) {
            $type = 'ARRAY[' . count($var) . ']';
        } else if (is_object($var)) {
            $type = 'OBJECT';
        } else if (is_resource($var)) {
            $type = 'RESOURCE';
        } else {
            $type = '???';
        }
        if (strlen($name)) {
            return logToConsole::logString("$type $name = " . var_export($var, true) . ';', $now);
        } else {
            return logToConsole::logString("$type = " . var_export($var, true) . ';', $now);
        }
    }

    /**
     * @param      $str
     * @param bool $now
     *
     * @return bool|void
     */
    public static function logString($str, $now = false)
    {
        if (false != $now) {
            $out = '';
            $out .= "<script type='text/javascript'>\n";
            $out .= "//<![CDATA[\n";
            $out .= "console.log(" . json_encode($str) . ");\n";
            $out .= "//]]>\n";
            $out .= "</script>";

            echo $out;

            return true;
        } else {
            register_shutdown_function('logToConsole::logString', $str, true);

            return true;
        }
    }
} 