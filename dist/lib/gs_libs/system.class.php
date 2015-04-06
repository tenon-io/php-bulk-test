<?php

/**
 * This class allows you to get direct access to certain bits
 * of system information and information about the PHP configuration
 */
class system
{

    /**
     * Class constructor
     * creates a list of the methods. This list can be filtered.
     * The list can then be called as a group of anonymous functions
     * or variable functions
     *
     * @param   mixed $methods
     */
    public function __construct($methods = null)
    {
        if (is_null($methods)) {
            $this->allMethods = get_class_methods($this);
        } else {
            if (is_array($methods)) {
                $allMethods = get_class_methods($this);
                foreach ($allMethods as $name) {
                    if (in_array($name, $methods)) {
                        $this->allMethods[] = $name;
                    }
                }
            } else {
                $allMethods = get_class_methods($this);
                foreach ($allMethods as $name) {
                    if ($name == $methods) {
                        $this->allMethods[] = $name;
                    }
                }
            }
        }
    }

    /**
     *
     * returns the system uptime
     *
     * @return string
     */
    public function getUptime()
    {
        return exec("uptime");
    }

    /**
     *
     * @return string
     */
    public function getSystemInformation()
    {
        return exec("uname -a");
    }

    /**
     *
     * returns disk usage information
     *
     * @return string
     */
    public function getDiskUsage()
    {
        return exec("df -h");
    }

    /**
     *
     * @return string
     */
    public function getCPUInfo()
    {
        return exec('cat /proc/cpuinfo');
    }

    /**
     *
     * Returns the amount of memory (in bytes) allocated to PHP
     *
     * @param   bool $real_usage Set this to TRUE to get the real size of memory allocated from system. If not set or FALSE only the memory used by emalloc() is reported.
     *
     * @return int
     */
    public function getMemUsage($real_usage = false)
    {
        return memory_get_usage($real_usage);
    }

    /**
     *
     * @param   bool $real_usage Set this to TRUE to get the real size of memory allocated from system. If not set or FALSE only the memory used by emalloc() is reported.
     *
     * @return int
     */
    public function getMaxMemUsage($real_usage = false)
    {
        return memory_get_peak_usage($real_usage);
    }

    /**
     * Gets percentage of memory used
     *
     * @return float
     */
    function getMemInfo()
    {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $memInfo = array();
        foreach ($data as $line) {
            list($key, $val) = explode(":", $line);
            $memInfo[$key] = trim($val);
        }

        return Math::getPercentage($memInfo['MemTotal'], $memInfo['MemFree']);
    }

    /**
     *
     * @return string
     */
    public function getSystemInfo()
    {
        return exec('systeminfo');
    }

    /**
     *
     * @return  string
     */
    public function getMemoryLimit()
    {
        return ini_get('memory_limit');
    }

    /**
     *
     * @return string
     */
    public function getMaxExecutionTime()
    {
        return ini_get('max_execution_time');
    }

    /**
     *
     * @return  string
     */
    public function getMaxInputTime()
    {
        return ini_get('max_input_time');
    }

    /**
     * will return an array with 3 keys  [0] = load now; [1] = load last 5 mins; [2] = last 15 mins
     *
     * @return array
     */
    public function getLoadAvg()
    {
        return sys_getloadavg();
    }

    /**
     * @param null $keys
     *
     * @return mixed
     */
    public function getEnv($keys = null)
    {
        if (is_null($keys)) {
            return $_ENV;
        } else {
            if (is_array($keys)) {
                foreach ($keys as $k => $v) {
                    $output[$k] = $_ENV[$v];
                }

                return $output;
            } else {
                return $_ENV[$keys];
            }
        }
    }

    /**
     * @param null $keys
     *
     * @return mixed
     */
    public function getServer($keys = null)
    {
        if (is_null($keys)) {
            return $_SERVER;
        } else {
            if (is_array($keys)) {
                foreach ($keys as $k => $v) {
                    $output[$k] = $_SERVER[$v];
                }

                return $output;
            } else {
                return $_SERVER[$keys];
            }
        }
    }

    /**
     * @param null $keys
     *
     * @return array|string
     */
    public function getIni($keys = null)
    {
        if (is_null($keys)) {
            return ini_get_all();
        } else {
            if (is_array($keys)) {
                foreach ($keys as $k => $v) {
                    $output[$k] = ini_get($v);
                }

                return $output;
            } else {
                return ini_get($keys);
            }
        }
    }

    /**
     * Function to get the environmental variables
     *
     * @param        string $Var
     *
     * @return        string    the desired variable
     */
    public static function get_env_var($Var)
    {
        if (empty($GLOBALS[$Var])) {
            $GLOBALS[$Var] = (!empty($GLOBALS['_SERVER'][$Var])) ? $GLOBALS['_SERVER'][$Var] : (!empty($GLOBALS['HTTP_SERVER_VARS'][$Var])) ? $GLOBALS['HTTP_SERVER_VARS'][$Var] : '';
        }
    }
}
