<?php

/**
 * This class parses Apache log files and turns the information into an array with the following keys
 * ip
 * access_time
 * page
 * type
 * success_code
 * bytes
 * referer
 * browser
 * uniqueID
 *
 * The latter key, 'uniqueID' is particularly important as it does not come from the log file itself
 * but is, instead, a hash of the record. This can be used to delineate one record from the next
 *
 */
class parseLogs
{

    public $pathToLogFile, $logArray;


    /**
     * Class constructor
     *
     * @param string $pathToLogFile path to the file which holds the server logs
     */
    public function __construct($pathToLogFile)
    {
        $this->pathToLogFile = $pathToLogFile;
    }

    /**
     * Grabs the server log file and turns it into an array.
     *
     */
    public function parse()
    {
        $ac_arr = file($this->pathToLogFile);
        $astring = join("", $ac_arr);
        $astring = preg_replace("/(\n|\r|\t)/", "", $astring);
        $records = preg_split("/([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $astring, -1, PREG_SPLIT_DELIM_CAPTURE);

        $sizerecs = sizeof($records);

        // now split into records
        $i = 1;
        $each_rec = 0;
        while ($i < $sizerecs) {
            $ip = $records[$i];
            $all = $records[$i + 1];

            // parse other fields
            preg_match("/\[(.+)\]/", $all, $match);
            $access_time = $match[1];
            $all = str_replace($match[1], "", $all);

            preg_match("/\"GET (.[^\"]+)/", $all, $match);
            $http = $match[1];
            $link = explode(" ", $http);
            $all = str_replace("\"GET $match[1]\"", "", $all);

            preg_match("/([0-9]{3})/", $all, $match);
            $success_code = $match[1];
            $all = str_replace($match[1], "", $all);

            preg_match("/\"(.[^\"]+)/", $all, $match);
            $ref = $match[1];
            $all = str_replace("\"$match[1]\"", "", $all);

            preg_match("/\"(.[^\"]+)/", $all, $match);
            $browser = $match[1];
            $all = str_replace("\"$match[1]\"", "", $all);

            preg_match("/([0-9]+\b)/", $all, $match);
            $bytes = $match[1];
            $all = str_replace($match[1], "", $all);

            $this->logArray[$each_rec]['ip'] = $ip;
            $this->logArray[$each_rec]['access_time'] = $access_time;
            $this->logArray[$each_rec]['page'] = $link[0];
            $this->logArray[$each_rec]['type'] = $link[1];
            $this->logArray[$each_rec]['success_code'] = $success_code;
            $this->logArray[$each_rec]['bytes'] = $bytes;
            $this->logArray[$each_rec]['referer'] = $ref;
            $this->logArray[$each_rec]['browser'] = $browser;
            $this->logArray[$each_rec]['uniqueID'] = md5(implode('', $this->logArray[$each_rec]));

            // advance to next record
            $i = $i + 2;
            $each_rec++;
        }

    }

}
