<?php

/**
 *
 * Class for doing certain utility functions
 * (basically things that cannot be logically organized into the other framework classes)
 */
class Utils
{

    /**
     *
     * @param   string $val
     * @param   string $trueVal
     * @param   string $falseVal
     *
     * @return  string
     */
    public static function getBoolToString($val, $trueVal = 'Yes', $falseVal = 'No')
    {
        if ($val == 1) {
            return $trueVal;
        } else {
            return $falseVal;
        }
    }

    /**
     *
     * returns the name of the defined variable
     *
     * @param    string
     * @param    int
     *
     * @return    string
     */
    public static function var_name(&$var, $scope = 0)
    {
        $old = $var;
        if (($key = array_search($var = 'unique' . rand() . 'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
            return $key;
        }

        return false;
    }

    /**
     *
     * simple boolean replacement for PHP's native unset
     * since 'unset' is a construct and not a function,
     * it has no return value, so there's no way of knowing
     * if the item was unset or not. (More specifically whether
     * it existed to be unset in the first place)
     *
     * @param   string
     *
     * @return    bool
     */
    public static function bool_unset($var)
    {
        if (isset($var)) {
            unset($var);

            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $path
     */
    public static function add_include_path($path)
    {
        foreach (func_get_args() AS $path) {
            if (!file_exists($path) || (file_exists($path) && filetype($path) !== 'dir')) {
                trigger_error("Include path '{$path}' not exists", E_USER_WARNING);
                continue;
            }

            $paths = explode(PATH_SEPARATOR, get_include_path());

            if (array_search($path, $paths) === false)
                array_push($paths, $path);

            set_include_path(implode(PATH_SEPARATOR, $paths));
        }
    }


    /**
     * Serializes and compresses data
     */
    public static function gSerialize($thing)
    {
        return base64_encode(gzcompress(serialize($thing)));
    }

    /**
     * Unserializes and uncompresses data
     */
    public static function gUnserialize($thing)
    {
        return unserialize(gzuncompress(base64_decode($thing)));
    }

    /**
     * returns variable info wrapped in PRE tags, for debugging
     */
    public static function gVarDump($var)
    {
        return '<pre>' . var_export($var, true) . '</pre>';
    }

    /**
     * recursively gets a list of all parents of a given class
     */
    public static function get_ancestors($class)
    {
        for ($classes[] = $class; $class = get_parent_class($class); $classes[] = $class) ;

        return $classes;
    }

}
