<?php

/**
 * Class DateExtended
 */
class DateExtended extends Date
{

    /**
     * class constructor: passes whatever arguments it receives back to
     * the parent class constructor
     */
    public function __construct()
    {
        parent::__construct(func_get_args());
    }


    /**
     * takes a 4-digit year and returns TRUE or FALSE
     * depending upon whether the year is a leap year
     *
     * @param $year
     *
     * @return bool
     */
    public static function isLeapYear($year)
    {
        return date('L', mktime(0, 0, 0, 1, 1, $year)) == 1 ? true : false;
    }

    /**
     * overrides the parent classes method of the same name
     *
     * @param bool $long
     *
     * @return bool|string
     */
    public function toLocaleString($long = false)
    {
        $output = '';

        if ($long) {
            $day = $this->getDayFullName();
            $date = $this->getOrdinalDate();
            $month = $this->getMonthFullName();
            $year = $this->getFullYear();
            $time = $this->getClockTime(true, true, false);

            $output = "$day, $date $month $year, $time";
        } else {
            $output = date('r', $this->getTime());
        }

        return $output;
    }

    /**
     * @param bool   $twelve
     * @param bool   $uppercaseAMPM
     * @param bool   $includeSeconds
     * @param string $separator
     *
     * @return string
     */
    public function getClockTime($twelve = true, $uppercaseAMPM = true, $includeSeconds = true, $separator = ':')
    {
        $am_pm = "";
        $hours = $this->getHours();

        if ($twelve) {
            $am_pm = " " . ($hours >= 12 ? "pm" : "am");

            if ($uppercaseAMPM) {
                $am_pm = strtoupper($am_pm);
            }

            if ($hours > 12) {
                $hours -= 12;
            }
        } else {
            // prepends $hours with a '0' if less than 10
            if ($hours < 10) {
                $hours = "0$hours";
            }
        }

        $minutes = $this->getMinutes();

        // prepends $minutes with a '0' if less than 10
        if ($minutes < 10) {
            $minutes = "0$minutes";
        }

        // prepends the separator before minutes
        $minutes = "$separator$minutes";


        $seconds = "";

        if ($includeSeconds) {
            $seconds = $this->getSeconds();

            // prepends $seconds with a '0' if less than 10
            if ($seconds < 10) {
                $seconds = "0$seconds";
            }

            // prepends the separator before seconds
            $seconds = "$separator$seconds";
        }

        return "$hours$minutes$seconds$am_pm";
    }

    /**
     * returns full English name of day of week
     * // (e.g. Sunday, Monday, etc.)
     *
     * @return bool|string
     */
    public function getDayFullName()
    {
        return date('l', $this->time);
    }

    /**
     * returns 3-letter abbreviation for day of week
     *
     * @return bool|string
     */
    public function getDayShortName()
    {
        return date('D', $this->time);
    }

    /**
     * returns number of days in current month
     *
     * @return bool|string
     */
    public function getDaysInMonth()
    {
        return date('t', $this->time);
    }

    /**
     * @param Date $date
     *
     * @return array
     */
    public function getDifference(Date $date)
    {
        $val1 = $this->getTime();
        $val2 = $this->getTime();
        $sec = abs($val2 - $val1);
        $units = getDate($sec);

        $hours = abs($units['hours'] - (date('Z') / 3600));

        $days = $units['mday'];

        if ($hours > 23) {
            $days++;
            $hours %= 24;
        }

        $output = array();


        $output['components'] = array(
            "years"   => $units['year'] - 1970,
            "months"  => --$units['mon'],
            "days"    => --$days,
            "hours"   => $hours,
            "minutes" => $units['minutes'],
            "seconds" => $units['seconds']
        );

        $output['elapsed'] = array(
            "years"   => $sec / (365 * 24 * 60 * 60),
            "months"  => $sec / (30 * 24 * 60 * 60),
            "weeks"   => $sec / (7 * 24 * 60 * 60),
            "days"    => $sec / (24 * 60 * 60),
            "hours"   => $sec / (60 * 60),
            "minutes" => $sec / 60,
            "seconds" => $sec
        );

        $output['order'] = $val2 < $val1 ? -1 : 1;

        return $output;
    }

    /**
     * returns full English name of month
     * (January, February, etc.)
     *
     * @return bool|string
     */
    public function getMonthFullName()
    {
        return date('F', $this->time);
    }

    /**
     * returns 3-letter abbreviation for month
     * (Jan, Feb, etc.)
     *
     * @return bool|string
     */
    public function getMonthShortName()
    {
        return date('M', $this->time);
    }

    /**
     * returns day of the month with an ordinal suffix
     * basically a wrapper for date('jS')
     *
     * @return bool|string
     */
    public function getOrdinalDate()
    {
        return date('jS', $this->time);
    }

    /**
     * returns name or abbreviation of current time zone
     *
     * @return bool|string
     */
    public function getTimeZoneName()
    {
        return date('T', $this->time);
    }

    /**
     * returns ISO week number
     *
     * @return int
     */
    public function getISOWeek()
    {
        return (int)date('W', $this->time);
    }

    /**
     * returns TRUE if current date/ time is DST
     *
     * @return bool
     */
    public function isDST()
    {
        return date('I', $this->time) == 1 ? true : false;
    }

    /**
     * returns TRUE if day is a weekday (Mon-Fri)
     *
     * @return bool
     */
    public function isWeekDay()
    {
        $w = $this->getDay();

        return ($w > 0 && $w < 6) ? true : false;
    }

    /**
     * returns ISO representation of date and time
     * e.g., 2005-03-26T18:59:07+10:00
     *
     * @return bool|string
     */
    public function toISOString()
    {
        return date('c', $this->time);
    }
}
