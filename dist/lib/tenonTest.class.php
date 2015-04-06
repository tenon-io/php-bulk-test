<?php

/**
 *    This class submits a request against the Tenon API for automatic
 *    accessibility testing.
 *
 */
class tenonTest
{
    public $url, $opts, $hash, $tenonResponse, $tURL;

    /**
     * Class constructor
     *
     * @param   string $url  the API url to post your request to
     * @param    array $opts options for the request
     */
    public function __construct($url, $opts)
    {
        $this->url = $url;
        $this->opts = $opts;
        $this->tenonResponse = '';
    }

    /**
     * Submits the HTML source for testing
     *
     * @param   bool $printInfo whether or not to print the output from curl_getinfo (usually for debugging only)
     *
     * @return    string    the results, formatted as JSON
     */
    public function submit($printInfo = false)
    {

        if (true == $printInfo) {
            echo '<h2>Options Passed To TenonTest</h2><pre><br>';
            var_dump($this->opts);
            echo '</pre>';
        }

        //open connection
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->opts);

        //execute post and get results
        $result = curl_exec($ch);

        if (true == $printInfo) {
            echo 'ERROR INFO (if any): ' . curl_error($ch) . '<br>';
            echo '<h2>Curl Info </h2><pre><br>';
            print_r(curl_getinfo($ch));
            echo '</pre>';
        }

        $this->tCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //close connection
        curl_close($ch);

        //the test results
        $this->tenonResponse = $result;
    }

    /**
     *
     * @param     $array
     * @param int $minlength
     *
     * @return array|bool
     */
    protected function cleanArray($array, $minlength = 32)
    {
        $output = array();

        if (!is_array($array)) {
            return false;
        }
        foreach ($array AS $val) {
            if (strlen($val) < $minlength) {
                continue;
            } else {
                $output[] = trim($val);
            }
        }

        return $output;
    }

    /**
     * @return mixed
     */
    public function decodeResponse()
    {
        if ((false !== $this->tenonResponse) && (!is_null($this->tenonResponse))) {
            $result = json_decode($this->tenonResponse, true);
            if (!is_null($result)) {
                $this->rspArray = $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}