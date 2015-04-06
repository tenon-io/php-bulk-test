<?php

/**
 * Class Date
 */
class Date
{
    // UNIX timestamp
    protected $time;

    // No. of minutes by which $time differs from UTC
    // takes an RFC-1123 date as its argument and returns a timestamp (in seconds)
    protected $offset;

    /**
     * CONSTRUCTOR
     * 4 optional ways of how this can be handled
     * No arguments: in this case, the Date instance corresponds to the current local date and time
     * One argument, of type INT: The argument is interpreted as a local timestamp in seconds
     * One argument, of type STRING: the argument is interpreted as a local date and time in RFC-1123 format
     * Two-to-Six arguments, all of type INT: handled similarly to the parse() method, above
     */
    public function __construct()
    {
        $num_args = func_num_args();

        // in the constructor has been called with at least one argument, then you assign the
        // arguments array to a variable named $args
        if ($num_args > 0) {
            $args = func_get_args();
        }

        // as an alternative to passing all of the arguments individually, they can be passed as an array
        // in the same order as accepted by the parse() method above.
        // if the first element of $args is itself an array, then assign this array to the variable $args
        // and update $num_args to hold the number of arguments in this array
        if (is_array($args[0])) {
            $args = $args[0];
            $num_args = count($args);
        }

        // if $num_args is greater than 1, that means that multiple arguments representing the different
        // portions of a date were passed to the constructor.
        // create and initialize variables to hold these values
        if ($num_args > 1) {
            $seconds = $minutes = $hours = $day = $month = $year = 0;
        }

        // use a SWITCH to set the values of variables that were passed in, in order.
        switch ($num_args) {
            case 6 :
                $seconds = $args[5];
            case 5 :
                $minutes = $args[4];
            case 4 :
                $hours = $args[3];
            case 3 :
                $day = $args[2];

            // you might notice there are no BREAK statements above.  This was intentional.
            // at this point, you have set all the temporary variables, so now they can be used
            // to make a call to mktime() and setting the $time variable to the result
            case 2 :
                $month = $args[1];
                $year = $args[0];
                $this->time = mktime($hours, $minutes, $seconds, ($month + 1), $day, $year);
                break;

            // if only a single argument was passed to the constructor, then we need to
            // check whether it was an integer or a string
            case 1 :
                // if it is an integer, we assume this to be a timestamp
                if (is_int($args[0])) {
                    $this->time = $args[0];
                } // if it is a string, we assume it to be an RFC-1123 formatted date
                elseif (is_string($args[0])) {
                    $this->time = strtotime($args[0]);
                }
                break;

            // if no arguments were passed to the constructor then we assign the current date and time
            case 0 :
                $this->$time = mktime();
                break;
        }

        // this next chunk gets the timezone offset
        $temp = gettimeofday();
        $this->offset = (int)$temp['minuteswest'];
    }

    /**
     * @param $date
     *
     * @return int
     */
    public static function parse($date)
    {
        return strtotime($date);
    }

    /**
     * Returns the UTC timestamp in seconds for a local date and time passed
     * to it as a set of three (required) to six (possible total) arguments
     * in the following order:
     * $year: a 4-digit year
     * $month: the number of the month (January=0, December=11)
     * $day: the day of the month (1-31)
     * $hours: Hours (0-23)
     * $minutes: Minutes (0-59)
     * $seconds: Seconds (0-59)
     *
     * @param $year
     * @param $month
     * @param $day
     *
     * @return int
     */
    public static function UTC($year, $month, $day)
    {
        $hours = $minutes = $seconds = 0;
        $num_args = func_num_args();
        // if there are more than 3 arguments,
        // $hours is the argument at index position 3
        if ($num_args > 3) {
            $hours = func_get_arg(3);
        }

        // if there are more than 4 arguments,
        // $minutes is the argument at index position 4
        if ($num_args > 4) {
            $minutes = func_get_arg(4) + ((int)date('Z') * 60);
        }

        // if there are more than 5 arguments,
        // $seconds is the argument at index position 5
        if ($num_args > 5) {
            $seconds = func_get_arg(5);
        }

        return mktime($hours, $minutes, $seconds, ($month + 1), $day, $year);
    }


    /**
     * return an implementation-dependent string representation of the local date and time
     * that is human-readable but DOES NOT use locale-specific formatting.
     * Return as mysql-style DATETIME format
     *
     * @return mixed
     */
    public function toString()
    {
        return str_replace('T', ' ', date('c', $this->time));
    }

    /**
     * returns day of month (1-31)
     *
     * @return int
     */
    public function getDate()
    {
        return (int)date("j", $this->time);
    }

    /**
     * returns day of week (0=Sunday, 6=Saturday)
     *
     * @return int
     */
    public function getDay()
    {
        return (int)date("w", $this->time);
    }

    /**
     * returns 4-digit year
     *
     * @return int
     */
    public function getFullYear()
    {
        return (int)date("Y", $this->time);
    }

    /**
     * returns hours (0-23)
     *
     * @return int
     */
    public function getHours()
    {
        return (int)date("H", $this->time);
    }

    /**
     * returns minutes (0-59)
     *
     * @return int
     */
    public function getMinutes()
    {
        return (int)date("i", $this->time);
    }

    /**
     * returns month (0=January, 11=December)
     *
     * @return int
     */
    public function getMonth()
    {
        $temp = (int)date("n", $this->time);

        return --$temp;
    }

    /**
     * returns seconds (0-59)
     *
     * @return int
     */
    public function getSeconds()
    {
        return (int)date("s", $this->time);
    }

    /**
     * returns a complete date as elapsed seconds
     * since the Unix epoch (midnight January 1, 1970 UTC)
     * note that this is not actually ECMA-compliant since it
     * returns seconds and not milliseconds
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * returns difference between local time and UTC
     * as measured in minutes
     * (east of Greenwich = positive, west of Greenwich = negative)
     *
     * @return int
     */
    public function getTimeOffset()
    {
        return $this->offset;
    }

    /**
     * the UTC-specific get*() methods below are defined in much the same way
     * except we use gmdate() instead of date().
     * returns day of month (1-31)
     *
     * @return int
     */
    public function getUTCDate()
    {
        return (int)gmdate("j", $this->time);
    }

    /**
     * returns day of week (0=Sunday, 6=Saturday)
     *
     * @return int
     */
    public function getUTCDay()
    {
        return (int)gmdate("w", $this->time);
    }

    /**
     * returns 4-digit year
     *
     * @return int
     */
    public function getUTCFullYear()
    {
        return (int)gmdate("Y", $this->time);
    }

    /**
     * returns hours (0-23)
     *
     * @return int
     */
    public function getUTCHours()
    {
        return (int)date("H", $this->time);
    }

    /**
     * returns minutes (0-59)
     *
     * @return int
     */
    public function getUTCMinutes()
    {
        return (int)date("i", $this->time);
    }

    /**
     * returns month (0=January, 11=December)
     *
     * @return int
     */
    public function getUTCMonth()
    {
        $temp = (int)date("n", $this->time);

        return --$temp;
    }

    /**
     * returns seconds (0-59)
     *
     * @return int
     */
    public function getUTCSeconds()
    {
        return (int)date("s", $this->time);
    }

    /**
     * set day of month (1-31)
     *
     * @param $date
     *
     * @return int
     */
    public function setDate($date)
    {
        $this->time = mktime($this->getHours(), $this->getMinutes(), $this->getSeconds(), $this->getMonth() + 1, $date, $this->getFullYear());

        return $this->time;
    }

    /**
     * set 4-digit year
     *
     * @param $year
     *
     * @return int
     */
    public function setFullYear($year)
    {
        $this->time = mktime($this->getHours(), $this->getMinutes(), $this->getSeconds(), $this->getMonth() + 1, $this->getDate(), $year);

        return $this->time;
    }

    /**
     * set hours (0-23)
     *
     * @param $hours
     *
     * @return int
     */
    public function setHours($hours)
    {
        $this->time = mktime($hours, $this->getMinutes(), $this->getSeconds(), $this->getMonth() + 1, $this->getDate(), $this->getFullYear());

        return $this->time;
    }

    /**
     * set minutes (0-59)
     *
     * @param $minutes
     *
     * @return Date
     */
    public function setMinutes($minutes)
    {
        $this->time = mktime($this->getHours(), $minutes, $this->getSeconds(), $this->getMonth() + 1, $this->getDate(), $this->getFullYear());

        return $this->time;
    }

    /**
     * set month (0-11)
     *
     * @param $month
     *
     * @return int
     */
    public function setMonth($month)
    {
        $this->time = mktime($this->getHours(), $this->getMinutes(), $this->getSeconds(), $month + 1, $this->getDate(), $this->getFullYear());

        return $this->time;
    }

    /**
     * set seconds (0-59)
     *
     * @param $seconds
     *
     * @return int
     */
    public function setSeconds($seconds)
    {
        $this->time = mktime($this->getHours(), $this->getMinutes(), $seconds, $this->getMonth() + 1, $this->getDate(), $this->getFullYear());

        return $this->time;
    }

    /**
     * set time in seconds since the Unix epoch
     * note that in ECMA-262 this should actually
     * be a value in milliseconds, not seconds
     *
     * @param $time
     *
     * @return int
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this->time;
    }

    /**
     * set time zone offsets in minutes
     * (negative values for points west of Greenwich,
     * positive values are east of it)
     *
     * @param $offset
     *
     * @return int
     */
    public function setTimeZoneOffset($offset)
    {
        $this->offset = $offset;

        return $this->time;
    }

    /**
     * set day of month (1-31) UTC
     *
     * @param $date
     *
     * @return int
     */
    public function setUTCDate($date)
    {
        $this->time = mktime($this->getUTCHours(), $this->getUTCMinutes() - $this->offset, $this->getUTCSeconds(), $this->getUTCMonth() + 1, $date, $this->getUTCFullYear());

        return $this->time;
    }

    /**
     * @param $year
     *
     * @return int
     */
    public function setUTCFullYear($year)
    {
        $this->time = mktime($this->getUTCHours(), $this->getUTCMinutes() - $this->offset, $this->getUTCSeconds(), $this->getUTCMonth() + 1, $this->getUTCDate(), $year);

        return $this->time;
    }

    /**
     * set hours (0-23) UTC
     *
     * @param $hours
     *
     * @return int
     */
    public function setUTCHours($hours)
    {
        $this->time = mktime($hours, $this->getUTCMinutes() - $this->offset, $this->getUTCSeconds(), $this->getUTCMonth() + 1, $this->getUTCDate(), $this->getUTCFullYear());

        return $this->time;
    }

    /**
     * set minutes (0-59) UTC
     *
     * @param $minutes
     *
     * @return int
     */
    public function setUTCMinutes($minutes)
    {
        $this->time = mktime($this->getUTCHours(), $minutes - $this->offset, $this->getUTCSeconds(), $this->getUTCMonth() + 1, $this->getUTCDate(), $this->getUTCFullYear());

        return $this->time;
    }

    /**
     * set month (0-11) UTC
     *
     * @param $month
     *
     * @return int
     */
    public function setUTCMonth($month)
    {
        $this->time = mktime($this->getUTCHours(), $this->getUTCMinutes() - $this->offset, $this->getUTCSeconds(), $month + 1, $this->getUTCDate(), $this->getUTCFullYear());

        return $this->time;
    }

    /**
     * set seconds (0-59) UTC
     *
     * @param $seconds
     *
     * @return int
     */
    public function setUTCSeconds($seconds)
    {
        $this->time = mktime($this->getUTCHours(), $this->getUTCMinutes() - $this->offset, $seconds, $this->getUTCMonth() + 1, $this->getUTCDate(), $this->getUTCFullYear());

        return $this->time;
    }

    /**
     * @return string
     */
    public function toGMTString()
    {
        return $this->toUTCString();
    }

    /**
     * returns the date formatted according to local
     * conventions and using local time
     *
     * @return bool|string
     */
    public function toLocaleString()
    {
        return date('r', $this->time);
    }

    /**
     * returns RFC-formatted date (see toGMTString()
     *
     * @return string
     */
    public function toUTCString()
    {
        return date("D d M Y H:i:s", ($this->time + ($this->offset * 60))) . " UTC";
    }

    /**
     * this is an alias for getTime()
     * once again ECMA specifies milliseconds rather than
     * seconds as it's implemented here
     *
     * @return int
     */
    public function valueOf()
    {
        return $this->time;
    }

    /**
     * creates an array of timezone options, each are pipe delimited for use in gForm option sets
     *
     * @return array
     */
    public function getTimezoneOpts()
    {
        // TIME ZONES ARRAY, IN PIPE FORMAT
        $timezone_opts[] = "(GMT - 12:00 hours) Enitwetok, Kwajalien|-12";
        $timezone_opts[] = "(GMT - 11:00 hours) Midway Island, Samoa|-11";
        $timezone_opts[] = "(GMT - 10:00 hours) Hawaii|-10";
        $timezone_opts[] = "(GMT - 9:00 hours) Alaska|-9";
        $timezone_opts[] = "(GMT - 8:00 hours) Pacific Time (US &amp; Canada)|-8";
        $timezone_opts[] = "(GMT - 7:00 hours) Mountain Time (US &amp; Canada)|-7";
        $timezone_opts[] = "(GMT - 6:00 hours) Central Time (US &amp; Canada), Mexico City|-6";
        $timezone_opts[] = "(GMT - 5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito|-5";
        $timezone_opts[] = "(GMT -4:30 hours) Caracas (Venezuela)|-4.5";
        $timezone_opts[] = "(GMT - 4:00 hours) Atlantic Time (Canada), La Paz|-4";
        $timezone_opts[] = "(GMT - 3:30 hours) Newfoundland|-3.5";
        $timezone_opts[] = "(GMT - 3:00 hours) Brazil, Buenos Aires, Georgetown, Falkland Is.|-3";
        $timezone_opts[] = "(GMT - 2:00 hours) Mid-Atlantic, Ascention Is., St Helena|-2";
        $timezone_opts[] = "(GMT - 1:00 hour) Azores, Cape Verde Islands|-1";
        $timezone_opts[] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia|0";
        $timezone_opts[] = "(GMT + 1:00 hour) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome, Warsaw|1";
        $timezone_opts[] = "(GMT + 2:00 hours) Kaliningrad, South Africa|2";
        $timezone_opts[] = "(GMT + 3:00 hours) Baghdad, Riyadh, Moscow, Nairobi|3";
        $timezone_opts[] = "(GMT + 3:30 hours) Tehran|3.5";
        $timezone_opts[] = "(GMT + 4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi|4";
        $timezone_opts[] = "(GMT + 4:30 hours) Kabul|4.5";
        $timezone_opts[] = "(GMT + 5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent|5";
        $timezone_opts[] = "(GMT + 5:30 hours) Bombay, Colomba, Calcutta, Madras, New Delhi|5.5";
        $timezone_opts[] = "(GMT + 5:45 hours) Nepal|5.75";
        $timezone_opts[] = "(GMT + 6:00 hours) Almaty, Dhakra|6";
        $timezone_opts[] = "(GMT + 7:00 hours) Bangkok, Hanoi, Jakarta|7";
        $timezone_opts[] = "(GMT + 8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei|8";
        $timezone_opts[] = "(GMT + 9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk|9";
        $timezone_opts[] = "(GMT + 9:30 hours) Adelaide, Darwin|9.5";
        $timezone_opts[] = "(GMT + 10:00 hours) Melbourne, Papua New Guinea, Sydney, Vladivostok|10";
        $timezone_opts[] = "(GMT + 11:00 hours) Magadan, New Caledonia, Solomon Islands|11";
        $timezone_opts[] = "(GMT + 12:00 hours) Auckland, Wellington, Fiji, Marshall Island|12";

        return $timezone_opts;
    }

    /**
     * @param $format
     *
     * @return bool|string
     */
    public function firstOfMonth($format)
    {
        return date("$format", strtotime(date('m') . '/01/' . date('Y') . ' 00:00:00'));
    }

    /**
     *
     * returns a count of the number of days in the specified month & year
     *
     * @param int $month
     * @param int $year
     *
     * @return int
     */
    public function daysInMonth($month, $year)
    {
        $date = mktime(12, 0, 0, $month, 1, $year);

        return date("t", $date);
    }

    /**
     *
     * returns the difference between two times
     *
     * @param        string $start the starting time for comparison
     * @param        string $end   the ending time for comparison
     *
     * @return        array    an array is populated with the time difference
     */
    public function timeDiff($start, $end)
    {
        $timediff = $end - $start;
        $days = intval($timediff / 86400);
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;

        $output = array();
        $output['days'] = $days;
        $output['hours'] = $hours;
        $output['minutes'] = $minutes;
        $output['seconds'] = $seconds;
        $output['full'] = $days . 'days ' . $hours . 'hrs ' . $mins . 'mins ' . $secs . 'sec ';

        return $output;
    }

    /**
     * @param int $yrs
     *
     * @return bool|string
     */
    function random_365($yrs = 1)
    {
        $today_date = date("d");
        $today_month = date("M");
        $today_year = date("Y");
        $next_year = $today_year + $yrs;

        if (!$time) {
            $time = strtotime("$today_date $today_month $today_year");
        }
        if (!$time2) {
            $time2 = strtotime("$today_date $today_month $next_year");
        }
        $timestamp = date("Y-m-d", rand(settype($time, "integer"), settype($time2, "integer")));
        //Must be called once before becoming random, ???
        $timestamp = date("Y-m-d", rand($time, $time2)) . " ";

        //Now it's random

        return $timestamp;
    }

    /**
     * @param int    $months
     * @param int    $days
     * @param int    $years
     * @param string $format
     *
     * @return bool|int|string
     */
    function past_date($months = 0, $days = 0, $years = 0, $format = "mysql")
    {
        $output = '';

        // MySQL DATE format YYYY-MM-DD
        if ($format == "mysql") {
            $output = date("Y-m-d", mktime(0, 0, 0, date("m") - $months, date("d") - $days, date("Y") - $years));

            return $output;
        } // MySQL DATETIME & TIMESTAMP format YYYY-MM-DD HH:MM:SS
        elseif ($format == "datetime") {
            $output = date("Y-m-d G:i:s", mktime(0, 0, 0, date("m") - $months, date("d") - $days, date("Y") - $years));

            return $output;
        } // Old style MySQL timestamp format YYYYMMDDHHMMSS
        elseif ($format == "oldtimestamp") {
            $output = date("YmdGis", mktime(0, 0, 0, date("m") - $months, date("d") - $days, date("Y") - $years));

            return $output;
        } // Traditional Unix Timestamp
        elseif ($format == "unixtimestamp") {
            $output = mktime(0, 0, 0, date("m") - $months, date("d") - $days, date("Y") - $years);

            return $output;
        } else {
            $output = date("DATE_ISO8601", mktime(0, 0, 0, date("m") - $months, date("d") - $days, date("Y") - $years));

            return $output;
        }
    }

    /**
     * gets a date in the future. Useful for comparing database records by date
     *
     * @param    int   $months the # of months in the future for the new date
     * @param    int   $days   the # of days in the future for the new date
     * @param    int   $years  the # of days in the future for the new date
     * @param   string $format
     *
     * @return    string    the date (in the future) is displayed in the desired format
     */
    function futureDate($months = 0, $days = 0, $years = 0, $format = "mysql")
    {
        $output = '';

        // MySQL DATE format YYYY-MM-DD
        if ($format == "mysql") {
            $output = date("Y-m-d", mktime(0, 0, 0, date("m") + $months, date("d") + $days, date("Y") + $years));

            return $output;
        } // MySQL DATETIME & TIMESTAMP format YYYY-MM-DD HH:MM:SS
        elseif ($format == "datetime") {
            $output = date("Y-m-d G:i:s", mktime(0, 0, 0, date("m") + $months, date("d") + $days, date("Y") + $years));

            return $output;
        } // Old style MySQL timestamp format YYYYMMDDHHMMSS
        elseif ($format == "oldtimestamp") {
            $output = date("YmdGis", mktime(0, 0, 0, date("m") + $months, date("d") + $days, date("Y") + $years));

            return $output;
        } // Traditional Unix Timestamp
        elseif ($format == "unixtimestamp") {
            $output = mktime(0, 0, 0, date("m") + $months, date("d") + $days, date("Y") + $years);

            return $output;
        } else {
            $output = date("DATE_ISO8601", mktime(0, 0, 0, date("m") + $months, date("d") + $days, date("Y") + $years));

            return $output;
        }
    }

    /**
     *
     * gets the full name of the month
     *
     * @param   int $m number representing the month
     *
     * @return    string  the long name of the month
     */
    function getMonthName($m = 0)
    {
        return (($m == 0) ? date("F") : date("F", mktime(0, 0, 0, $m)));
    }

    /**
     * turns a datetime into an array
     *
     * @param    string $datetime   the datetime formatted as:
     *                              YYYY-MM-DD HH:MM:SS
     *
     * @return    array
     */
    function datetimeToArray($datetime)
    {
        $timestamp = strtotime($datetime);
        $date['y'] = date('Y', $timestamp);
        $date['m'] = date('m', $timestamp);
        $date['d'] = date('d', $timestamp);
        $date['h'] = date('H', $timestamp);
        $date['i'] = date('i', $timestamp);
        $date['s'] = date('s', $timestamp);

        return $date;
    }

    /**
     *
     * gets the string name of the day of the week for a specific date
     *
     * @param    string $date the date, arranged as 'mm-dd-yyyy'
     *
     * @return    string
     */
    function dayOfWeek($date)
    {
        $total = 0;
        $date = explode("-", $date);

        if ($date[0] <= 1752) {
            // if the date is prior to 1752 then subtract the century from 18
            $centry = substr($date[0], 0, 2);
            $total = (18 - $centry);
        } else {
            // otherwise do it this way
            // extract Century
            $centry = substr($date[0], 0, 2);
            // divide by 4 - take remainder by 3 multiply by 2
            $total = ((3 - ($centry % 4)) * 2);
        }

        // if total is more than seven then divide by 7 and take remainder
        if ($total > 7) {
            $total = $total % 7;
        }

        // extract year
        $year = $date[0] % 100;
        // divide by 12, add this to remainder, add number of 4s in the remainder
        $year = (((int)($year / 12)) + ($year % 12) + ((int)(($year % 12) / 4)));
        // add the year to the total
        $total = $centry + $year;

        // if total is more than seven then divide by 7 and take remainder
        if ($total > 7) {
            $total = $total % 7;
        }

        // extract month
        $month = $date[1];
        // pick month from table
        $monthTable = array(0, 3, 3, 6, 1, 4, 6, 2, 5, 0, 3, 5);
        $month = $monthTable[(int)$month - 1];
        // add month to total
        $total = $total + $month;

        // extract day
        $day = $date[2];

        // if the year is a leap year, and the month is January or February then minus 1.
        if ($date[0] % 4 == 0) {
            if ($date[0] % 100 == 0) {
                if ($date[0] % 400 == 0) {
                    if ($date[1] == 1 || $date[1] == 2) {
                        $day = $day - 1;
                    }
                }
            } else {
                if ($date[1] == 1 || $date[1] == 2) {
                    $day = $day - 1;
                }
            }
        }

        // add to total
        $total = $total + $day;

        // if total is more than seven then divide by 7 and take remainder
        if ($total > 7) {
            $total = $total % 7;
        }

        // convert day number into day
        $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

        return $days[$total];
    }

    /**
     *
     * compares one time against another
     * !!!Dates Must be formatted as YYYY-MM-DD HH:MM:SS!!!
     *
     * @param   string  $start_time the start time of the comparison in the format YYYY-MM-DD HH:MM:SS
     * @param    string $end_time   the end time of the comparison in the format YYYY-MM-DD HH:MM:SS
     *
     * @return    array   an array of useful formations for the difference
     */
    function compareDatetime($start_time, $end_time)
    {

        // first, fix the start time
        $start_time = preg_replace('/^([0-9]+)$/', '', $start_time);
        $start_year = substr($start_time, 0, 4);
        $start_month = substr($start_time, 4, 2);
        $start_day = substr($start_time, 6, 2);
        $start_hour = substr($start_time, 8, 2);
        $start_minute = substr($start_time, 10, 2);
        $start_second = substr($start_time, 12, 2);
        $start_time = mktime($start_hour, $start_minute, $start_second, $start_month, $start_day, $start_year);

        // fix the end time
        $end_time = preg_replace('/^([0-9]+)$/', '', $end_time);
        $end_year = substr($end_time, 0, 4);
        $end_month = substr($end_time, 4, 2);
        $end_day = substr($end_time, 6, 2);
        $end_hour = substr($end_time, 8, 2);
        $end_minute = substr($end_time, 10, 2);
        $end_second = substr($end_time, 12, 2);
        $end_time = mktime($end_hour, $end_minute, $end_second, $end_month, $end_day, $end_year);

        // calculate the difference
        $diff = $end_time - $start_time;
        $output['days'] = ($diff - ($diff % 86400)) / 86400;

        $diff = $diff - ($days * 86400);
        $output['hours'] = ($diff - ($diff % 3600)) / 3600;

        $diff = $diff - ($hours * 3600);
        $output['minutes'] = ($diff - ($diff % 60)) / 60;

        $diff = $diff - ($minutes * 60);
        $output['seconds'] = ($diff - ($diff % 1)) / 1;

        $output['full'] = $output['days'] . "days" . $output['hours'] . "hr" . $output['mins'] . "min" . $output['secs'] . "s";

        return $output;
    }

    /**
     * calculates an age (usually a person), in years
     *
     * @param    int $m the person's birth month
     * @param    int $d the person's birth day
     * @param    int $y the person's birth year
     *
     * @return    int        the year as calculated
     */
    function calculateAge($m, $d, $y)
    {
        $ageTime = mktime(0, 0, 0, $m, $d, $y);
        // Get the person's birthday as a timestamp
        $t = time();
        // Store current time for comparison
        $age = ($ageTime < 0) ? ($t + ($ageTime * -1)) : $t - $ageTime;
        $year = 60 * 60 * 24 * 365;
        $ageYears = $age / $year;
        $age = floor($ageYears);

        return $age;
    }

    /**
     * generates time of day to millisecond precision
     *
     * @return  int
     */
    function utime()
    {
        $t = gettimeofday();

        return (float)($t['sec'] + $t['usec'] / 1000000.0);
    }

    /**
     *
     * converts a mysql date timestamp to something people can actually read.
     *
     * @param    string $date
     *
     * @return    string    a human-readable reformatting of a mysql
     * timestamp
     */
    function timestamp_db_php($date)
    {
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $hour = substr($date, 8, 2);
        $minute = substr($date, 10, 2);
        $second = substr($date, 12, 2);
        $datetime = $month . "-" . $day . "-" . $year . " &nbsp;" . $hour . ":" . $minute . ":" . $second . " ";

        return $datetime;
    }

    /**
     * fix date from the MySQL date format to the american style date
     *
     * @author    Michael Belt http://www.michaelbelt.com
     *
     * @param    string $date the date string to be converted
     *
     * @return    string
     */
    function american_date($date)
    {
        // convert the date from the lame-o  mySQL format of 0000-00-00 and convert it
        // it will now display in an American style month-name, date, and year format such as January 10, 2003
        list($year, $month, $day) = explode("-", $date);
        $american_date = date("l, F d, Y", mktime(0, 0, 0, $month, $day, $year));

        return $american_date;
    }

    /**
     * converts mysql datetime format to unix timestamp format
     *
     * @param   string $mysql_time the time, arriving in the format of mysql datetime
     *
     * @return  string
     */
    function mySQL2TS($mysql_time)
    {
        if (!preg_match('/^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$/', $mysql_time, $matches)) {
            return null;
        }

        return mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
    }

    /**
     *
     * gets the current date formatted for use with mySQL
     *
     * @return string
     */
    public function mySQLToday()
    {
        return date("Y-m-d");
    }

    /**
     *
     * gets yesterday's date formatted for use with mySQL
     *
     * @return  string
     */
    public function mySQLYesterday()
    {
        return date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
    }

    /**
     *
     * gets a mySQL formatted string from a specified timestamp
     *
     * @param   int $time
     *
     * @return  string
     */
    public function mySQLArbDate($time)
    {
        return date("Y-m-d", $time);
    }

    /**
     * creates an array of days (of the month). Useful for day dropdowns in gForm
     *
     * @return array
     */
    public static function getDayOptsArray()
    {
        return array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");

    }

    /**
     * creates an array of months. Useful for day dropdowns in gForm
     *
     * @return array
     */
    public static function getMonthOptsArray()
    {
        return array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");

    }

    /**
     * gets an array of years. useful for year dropdowns in gForm
     *
     * @param   int $futureYears how many years into the future to include
     * @param   int $pastYears   how many years into the past to include
     *
     * @return  array
     */
    public static function getYearOptsArray($futureYears = 0, $pastYears = 0)
    {
        $thisYear = date('Y');

        $opts = array();
        $opts[] = $thisYear;

        if ($pastYears > 0) {
            for ($x = 0; $x < $pastYears; $x++) {
                $opts[] = $thisYear - $x;
            }
        }

        if ($futureYears > 0) {
            for ($x = 0; $x < $futureYears; $x++) {
                $opts[] = $thisYear + $x;
            }
        }

        return sort($opts, SORT_NUMERIC);

    }

    /**
     * returns current UTC timestamp date in MySQL datetime format
     *
     * @return  string
     */
    public static function UTC2MySQLDateTime()
    {
        return gmdate('Y-m-d G:i:s');
    }

    /**
     * checks a datetime formatted date to ensure it is valid
     *
     * @param   string $datetime the date string
     *
     * @return  bool
     */
    function checkDateTime($datetime)
    {
        $date = strtotime($datetime);

        return checkdate(date('m', $date), date('d', $date), date('Y', $date));
    }

    /**
     * Converts a date from one timezone to another.  Generally for changing from one
     * stored date (as UTC) to the user's timezone
     *
     * @param   string $storedTime the stored time
     * @param   string $storedZone the timezone that the time was stored in
     * @param   string $userZone   the timezone to convert the time to
     * @param   string $format     format (for use in date() ) for the date
     *
     * @return  string
     */
    function dateConvert($storedTime, $storedZone, $userZone, $format)
    {
        $gmtTimezone = new DateTimeZone($storedZone);
        $userTimezone = new DateTimeZone($userZone);
        $userDateTime = new DateTime($storedTime, $gmtTimezone);
        $offset = $userTimezone->getOffset($userDateTime);

        return date($format, $userDateTime->format('U') . $offset);
    }

    /**
     * Determines the number - from 01-31 - representing the last day of the specified month and specified year
     *
     * @param null $month (optional)
     * @param null $year  (optional)
     *
     * @return bool|string
     */
    public function getLastDayOfTheMonth($month = null, $year = null)
    {
        if (is_null($month)) {
            $month = date('m');
        }
        if (is_null($year)) {
            $year = date('Y');
        }

        $theDate = $year . '-' . $month . '-' . '01';

        return date('t', strtotime($theDate));
    }


    /**
     *  counts the dates within a specific range
     *
     * @param   string $start (optional) Must be 'YYYY-MM-DD'. Date representing the first date from which stats will be pulled
     * @param   string $end   (optional) Must be 'YYYY-MM-DD'. Date representing the last date from which stats will be pulled
     *
     * @return  int
     */
    public function countDates($start = null, $end = null)
    {
        // make timestamps from the MySQL formatted time
        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);

        // First we need to break these dates into their constituent parts:
        $gd_a = getdate($startTimestamp);
        $gd_b = getdate($endTimestamp);

        // Now recreate these timestamps, based upon noon on each day
        // The specific time doesn't matter but it must be the same each day
        $a_new = mktime(12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year']);
        $b_new = mktime(12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year']);

        // Subtract these two numbers and divide by the number of seconds in a
        //  day. Round the result since crossing over a daylight savings time
        //  barrier will cause this time to be off by an hour or two.
        return round(abs($a_new - $b_new) / 86400);
    }


    /**
     *
     * Creates an array of all dates between $start and $end
     *
     * Stolen from http://stackoverflow.com/questions/4312439/php-return-all-dates-between-two-dates-in-an-array
     *
     * @param string $start Must be 'YYYY-MM-DD'. Date representing the first date from which stats will be pulled
     * @param string $end   Must be 'YYYY-MM-DD'. Date representing the last date from which stats will be pulled
     * @param string $format
     *
     * @return array
     */
    public function createDateRangeArray($start, $end, $format = 'Y-m-d')
    {

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($start, 5, 2), substr($start, 8, 2), substr($start, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($end, 5, 2), substr($end, 8, 2), substr($end, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date($format, $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date($format, $iDateFrom));
            }
        }

        return $aryRange;
    }


    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countSecondsBetween($start, $end, $precision = 2)
    {
        $start = strtotime($start);
        $end = strtotime($end);

        if ($start > $end) {
            $big = $start;
            $small = $end;
        } else {
            $big = $end;
            $small = $start;
        }

        $diff = $big - $small;

        return round($diff, $precision);
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countMinutesBetween($start, $end, $precision = 2)
    {
        $diff = Date::countSecondsBetween($start, $end, 5);

        return round($diff / 60, $precision);
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countHoursBetween($start, $end, $precision = 2)
    {
        $diff = Date::countSecondsBetween($start, $end, 5);

        return round(($diff / 60) / 60, $precision);
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countDaysBetween($start, $end, $precision = 2)
    {
        $diff = Date::countSecondsBetween($start, $end, 5);

        return round((($diff / 60) / 60) / 24, $precision);
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countYearsBetween($start, $end, $precision = 2)
    {
        return round(Date::countDaysBetween($start, $end, $precision) / 365, $precision);
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countWeeksBetween($start, $end, $precision = 2)
    {
        $diff = Date::countSecondsBetween($start, $end, 5);

        return round(((($diff / 60) / 60) / 24) / 7, $precision);
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countMonthsBetween($start, $end, $precision = 2)
    {
        return Date::countYearsBetween($start, $end, 5) * 12;
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countDecadesBetween($start, $end, $precision = 2)
    {
        return Date::countYearsBetween($start, $end, 5) / 10;
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countCenturiesBetween($start, $end, $precision = 2)
    {
        return Date::countYearsBetween($start, $end, 5) / 100;
    }

    /**
     * @param     $start
     * @param     $end
     * @param int $precision
     *
     * @return float
     */
    public function countMilleniaBetween($start, $end, $precision = 2)
    {
        return Date::countYearsBetween($start, $end, 5) / 1000;
    }
}