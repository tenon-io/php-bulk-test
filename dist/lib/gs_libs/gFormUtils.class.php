<?php

/**
 *
 * This class contains a number of methods used to work with the gForm class
 * to assist in presentation and validation of forms.
 * This separate file was created to keep the class file clean.
 * Needless to say, this file needs to be called as an include in the same file
 * which includes the main class.
 *
 */
class gFormUtils
{

    /**
     *
     * detects multiple forms of abuse in form submissions
     *
     * @param string $val
     * @param array  $bad_strings
     * @param bool   $badstrs
     * @param bool   $newlines
     * @param bool   $bogusmail
     *
     * @return bool
     */
    public static function detect_abuse($val, $bad_strings, $badstrs = false, $newlines = false, $bogusmail = false)
    {
        // detect bad strings
        if ($badstrs == true) {
            foreach ($bad_strings as $bad_string) {
                if (eregi($bad_string, strtolower(${"$val"}))) {
                    return true;
                }
            }
        }

        // detect new lines
        if ($newlines == true) {
            if (preg_match("/(%0A|%0D|\\n+|\\r+)/i", $val) != 0) {
                return true;
            }
        }

        // detect bogus e-mail addresses
        if ($bogusmail == true) {
            $item_match = explode("@", $val);
            if (in_array($item_match[1], $param)) {
                return true;
            }
        }
    }

    /**
     *
     * checks whether a date is valid
     *
     * @param    string $date must be in M-D-Y format
     *
     * @return    bool    true if valid, false otherwise
     */
    public static function check_date($date)
    {
        if (!isset($date) || $date == "") {
            return false;
        }

        list ($mm, $dd, $yy) = explode("-", $date);

        if ($dd != "" && $mm != "" && $yy != "") {
            if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd)) {
                return checkdate($mm, $dd, $yy);
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     *
     * checks whether a year is valid
     *
     * @param    int $year the year to be validated
     *
     * @return    bool
     */
    public static function validyear($year)
    {
        if (strlen($year) == 4) {
            if (checkdate(1, 1, $year)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     *
     * checks whether a date occurs in the past
     *
     * @param    int  $month  the month to be validated
     * @param    int  $day    the day to be validated
     * @param    int  $year   the year to be validated
     * @param    bool $strict whether to include a strict validation
     *                        that the date supplied actually exists
     *                        Defaults to TRUE
     *
     * @return    bool
     */
    public static function date_not_past($month, $day, $year, $strict = true)
    {
        // First check the year
        if ($year < date("Y")) {
            return false;
        } // Then check the month (if the year is this year)
        elseif ($year == date("Y") && $month < date("m")) {
            return false;
        } // Then check the day (if the year is this year and month is this month)
        elseif ($year == date("Y") && $month == date("m") && $day < date("d")) {
            return false;
        } // Check to see if the date supplied is today
        elseif ($year . $month . $day == date("Ymd")) {
            return true;
        } // Do an optional check to ensure the date actually exists
        elseif ((!self::check_date($month . "-" . $day . "-" . $year)) && ($strict == true)) {
            return false;
        } // If none of the above criteria are false, then the date must be in the future.
        else {
            return true;
        }
    }

    /**
     *
     * checks whether an array of keys matches the keys of an array
     * in other words, given a list of keys that *should* be in the
     * array, are they there?
     *
     * @param    array $array the array to check
     * @param    array $rkeys the required keys to check for
     *
     * @return    bool
     */
    public static function check_array_keys($array, $rkeys)
    {
        foreach ($rkeys AS $val) {
            if (!array_key_exists($val, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * function will validate e-mail addresses
     * implement this function as 'MailVal($Addr, $Level)'
     *
     * @todo    this function is old. TLDs and CCs may be out of date.
     *
     * @param    string $Addr    the e-mail address to be validated
     * @param    int    $Level   level, between 1 and 5 in ascending
     *                           order of oppressiveness
     * @param    int    $Timeout length, in seconds, the function
     *                           will execute before it times out
     *
     * @return    Bool
     */
    public static function MailVal($Addr, $Level, $Timeout = 15000)
    {

        //  Valid Top-Level Domains
        $gTLDs = "com:net:org:edu:gov:mil:int:arpa:name:tv:info:asia:biz:coop:jobs:mobi:museum:pro:tel:travel:xxx";
        $CCs = "ad:ae:af:ag:ai:al:am:an:ao:aq:ar:as:at:au:aw:az:ba:bb:bd:be:bf:" .
            "bg:bh:bi:bj:bm:bn:bo:br:bs:bt:bv:bw:by:bz:ca:cc:cf:cd:cg:ch:ci:" .
            "ck:cl:cm:cn:co:cr:cs:cu:cv:cx:cy:cz:de:dj:dk:dm:do:dz:ec:ee:eg:" .
            "eh:er:es:et:fi:fj:fk:fm:fo:fr:fx:ga:gb:gd:ge:gf:gh:gi:gl:gm:gn:" .
            "gp:gq:gr:gs:gt:gu:gw:gy:hk:hm:hn:hr:ht:hu:id:ie:il:in:io:iq:ir:" .
            "is:it:jm:jo:jp:ke:kg:kh:ki:km:kn:kp:kr:kw:ky:kz:la:lb:lc:li:lk:" .
            "lr:ls:lt:lu:lv:ly:ma:mc:md:mg:mh:mk:ml:mm:mn:mo:mp:mq:mr:ms:mt:" .
            "mu:mv:mw:mx:my:mz:na:nc:ne:nf:ng:ni:nl:no:np:nr:nt:nu:nz:om:pa:" .
            "pe:pf:pg:ph:pk:pl:pm:pn:pr:pt:pw:py:qa:re:ro:ru:rw:sa:sb:sc:sd:" .
            "se:sg:sh:si:sj:sk:sl:sm:sn:so:sr:st:su:sv:sy:sz:tc:td:tf:tg:th:" .
            "tj:tk:tm:tn:to:tp:tr:tt:tv:tw:tz:ua:ug:uk:um:us:uy:uz:va:vc:ve:" .
            "vg:vi:vn:vu:wf:ws:ye:yt:yu:za:zm:zr:zw:";

        //  The countries can have their own 'TLDs', e.g. mydomain.com.au
        $cTLDs = "com:net:org:edu:gov:mil:co:ne:or:ed:go:mi:eu";

        $fail = 0;

        //  Shift the address to lowercase to simplify checking
        $Addr = strtolower($Addr);

        //  Split the Address into user and domain parts
        $UD = explode("@", $Addr);
        if (sizeof($UD) != 2 || !$UD[0])
            $fail = 1;

        //  Split the domain part into its Levels
        $Levels = explode(".", $UD[1]);
        $sLevels = sizeof($Levels);
        if ($sLevels < 2)
            $fail = 1;

        //  Get the TLD, strip off trailing ] } ) > and check the length
        $tld = $Levels[$sLevels - 1];
        $tld = ereg_replace("[>)}]$|]$", "", $tld);
        if (strlen($tld) < 2 || strlen($tld) > 3 && $tld != "arpa")
            $fail = 1;

        $Level--;

        //  If the string after the last dot isn't in the generic TLDs or country codes, it's invalid.
        if ($Level && !$fail) {
            $Level--;
            if (!ereg($tld . ":", $gTLDs) && !ereg($tld . ":", $CCs))
                $fail = 2;
        }

        //  If it's a country code, check for a country TLD; add on the domain name.
        if ($Level && !$fail) {
            $cd = $sLevels - 2;
            $domain = $Levels[$cd] . "." . $tld;
            if (ereg($Levels[$cd] . ":", $cTLDs)) {
                $cd--;
                $domain = $Levels[$cd] . "." . $domain;
            }
        }

        //  See if there's an MX record for the domain
        if ($Level && !$fail) {
            $Level--;
            if (!getmxrr($domain, $mxhosts, $weight))
                $fail = 3;
        }

        //  Attempt to connect to port 25 on an MX host
        if ($Level && !$fail) {
            $Level--;
            while (!$sh && list($nul, $mxhost) = each($mxhosts))
                $sh = fsockopen($mxhost, 25);
            if (!$sh)
                $fail = 4;
        }

        //  See if anyone answers
        if ($Level && !$fail) {
            $Level--;
            set_socket_blocking($sh, false);
            $out = "";
            $t = 0;
            while ($t++ < $Timeout && !$out)
                $out = fgets($sh, 256);
            if (!ereg("^220", $out))
                $fail = 5;
        }

        if ($sh)
            fclose($sh);

        return $fail;
    }

    /**
     *
     * uses regex check to see if IP address is valid
     *
     * @param    string $ip the IP address we're validating
     *
     * @return    bool
     */
    public static function is_valid_ip($ip)
    {
        if (ereg("^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$", $ip)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param   string $recipient
     * @param   string $sender
     * @param   string $subject
     * @param   string $message
     *
     * @return  bool
     */
    public static function gFormEmail($recipient, $sender, $subject, $message)
    {
        // define the headers
        $headers = "";
        $headers = "From: $sender <$sender>\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Reply-To: $sender <$sender>\n";
        $headers .= "Return-Path: $sender <$sender>\n";
        $headers .= "X-Sender: <$sender>\n";
        $headers .= "X-Originating-IP: " . Network::getIP() . " \n";
        $headers .= "Errors-To: <$sender>\n";
        $headers .= "Message-ID: <" . md5(uniqid(rand())) . ">\n";
        $headers .= "X-Mailer: PHP v" . phpversion() . "\n";
        $headers .= "X-Priority: 3\n";
        $headers .= "Content-Type: text/plain; charset=iso-8859-1\n"; // sets the mime type

        return mail($recipient, $subject, stripslashes(wordwrap($message, 72)), $headers);
    }

    /**
     *
     * creates a datalist element and all its options
     *
     * @param   array  $array
     * @param   string $id
     *
     * @return  string
     */
    public static function arrayToDataList($array, $id)
    {
        $output = '';
        $output .= "<datalist id=\"$id\">";
        foreach ($array AS $key => $val) {
            $output .= "<option label=\"$key\" value=\"$val\"></option>";
        }
        $output .= "</datalist>";

        return $output;
    }

    /**
     *
     * Checks to see if a date is between a specified start and end date
     * :GOTCHA  Uses strtotime. You are responsible for understanding how strtotime works!
     *
     * @param   string $x
     * @param   string $start
     * @param   string $end
     *
     * @return  bool
     */
    public static function isDateBetween($x, $start, $end)
    {
        //@todo need some magic to make sure $end is actually after $start
        $dX = strtotime($x);
        $dStart = strtotime($start);
        $dEnd = strtotime($end);

        return Math::isBetween($dX, $dStart, $dEnd);
    }

    /**
     *
     * @param    string $val
     *
     * @return  string
     */
    function getBoolToString($val)
    {
        if ($val == 1) {
            return _('Yes');
        } else {
            return _('No');
        }
    }
}
