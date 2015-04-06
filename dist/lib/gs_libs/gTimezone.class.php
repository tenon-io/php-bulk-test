<?php

/**
 * Class gTimezone
 */
class gTimezone extends DateExtended
{

    /**
     * returns a "friendly" list of timezone options into an array
     *
     * @return  array
     */
    public function getTimezoneOpts()
    {
        $locations = '';
        $zones = timezone_identifiers_list();

        foreach ($zones as $zone) {
            $zone = explode('/', $zone);
            // 0 => Continent, 1 => City

            // Only use "friendly" continent names
            if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific') {
                if (isset($zone[1]) != '') {
                    $locations[$zone[0]][$zone[0] . '/' . $zone[1]] = str_replace('_', ' ', $zone[1]);
                    // Creates array(DateTimeZone => 'Friendly name')
                }
            }
        }

        return $locations;
    }

    /**
     *
     * Converts time from one timezone to the next
     * Inspired by http://truelogic.org/wordpress/2011/05/22/php-function-to-convert-time-from-one-timezone-to-another/
     * USAGE: Gets a GMT date and turns it into a US East Coast Time
     * $timezone = new gTimezone();
     * $usEastCoast = $timezone->(0, “2011-05-09 11:00:00″, -5);
     *
     * @param   int    $conv_fr_zon  the 'from' timezone for the date to be converted, representing deviation from GMT (such as -5 for US Eastern Time)
     * @param   string $conv_fr_time the time to be converted
     * @param   int    $conv_to_zon  the 'to' timezone for the new date, representing deviation from GMT (such as -5 for US Eastern Time)
     *
     * @return  string
     */
    public function convertTime($conv_fr_zon = 0, $conv_fr_time = "", $conv_to_zon = 0)
    {
        //echo $conv_fr_zon."<br>";
        $cd = strtotime($conv_fr_time);

        $gmdate = date('Y-m-d H:i:s', mktime(date('H', $cd) - $conv_fr_zon, date('i', $cd), date('s', $cd), date('m', $cd), date('d', $cd), date('Y', $cd)));
        //echo $gmdate."<br>";

        $gm_timestamp = strtotime($gmdate);
        $finaldate = date('Y-m-d H:i:s', mktime(date('H', $gm_timestamp) + $conv_to_zon, date('i', $gm_timestamp), date('s', $gm_timestamp), date('m', $gm_timestamp), date('d', $gm_timestamp), date('Y', $gm_timestamp)));

        return $finaldate;
    }

}