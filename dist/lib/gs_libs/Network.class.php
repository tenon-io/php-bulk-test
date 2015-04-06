<?php

/**
 *
 * Function library for dealing with server/ network stuff
 *
 */
class Network
{

    /**
     * Function to get the user's IP address
     *
     * @return  string
     */
    public static function getip()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED'];
            } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
                $realip = $_SERVER['HTTP_FORWARDED'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } elseif (getenv('HTTP_X_FORWARDED')) {
                $realip = getenv('HTTP_X_FORWARDED');
            } elseif (getenv('HTTP_FORWARDED_FOR')) {
                $realip = getenv('HTTP_FORWARDED_FOR');
            } elseif (getenv('HTTP_FORWARDED')) {
                $realip = getenv('HTTP_FORWARDED');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }

        return $realip;
    }

    /**
     * validates whether or not an URL is good
     *
     * @param    string $link
     * @param bool      $strict
     *
     * @return    bool
     */
    public static function urlValidate($link, $strict = true)
    {
        if (strlen($link) < 6) {
            return false;
        }

        $url_parts = @parse_url($link);

        if (empty($url_parts['host'])) {
            return false;
        }

        if (!empty($url_parts['path'])) {
            $documentpath = $url_parts['path'];
        } else {
            $documentpath = "/";
        }

        if (!empty($url_parts['query'])) {
            $documentpath .= "?" . $url_parts['query'];
        }

        $host = $url_parts['host'];
        $port = $url_parts['port'];

        if (empty($port)) {
            $port = "80";
        }

        $socket = @fsockopen($host, $port, $errno, $errstr, 30);

        if (!$socket) {
            return false;
        } else {
            fwrite($socket, "HEAD " . $documentpath . " HTTP/1.0\r\nHost: $host\r\n\r\n");
            $http_response = fgets($socket, 22);

            // If we're doing a strict check, only look for 200 Response
            if ($strict == true) {
                if (Strings::contains('200 OK', $http_response, true)) {
                    fclose($socket);

                    return true;
                }
            } // If we're not doing a strict check, look for 200, 301, 302
            else {
                if (Strings::contains('200 OK', $http_response, true)) {
                    fclose($socket);

                    return true;
                } elseif (Strings::contains('301', $http_response, true)) {
                    fclose($socket);

                    return true;
                } elseif (Strings::contains('302', $http_response, true)) {
                    fclose($socket);

                    return true;
                } else {
                    //echo "HTTP-Response: $http_response<br>";
                    return false;
                }
            }
        }
    }

    /**
     * @param   $theURL
     * @param   $ua
     *
     * @return string
     */
    public static function get_http_response_code($theURL, $ua = null)
    {
        if (false === stripos($theURL, 'http')) {
            return false;
        }
        if (is_null($ua)) {
            $ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36';
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $theURL);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $r = curl_exec($ch);

        return curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }

    /**
     * @param      $url
     * @param null $opts
     *
     * @return mixed
     */
    public static function get_http_response_info($url, $opts = null)
    {
        if (!is_array($opts)) {
            $opts = array(
                'useragent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36',
                'connecttimeout' => 30, // timeout on connect
                'timeout'        => 30, // timeout on response
                'redirect'       => 0,
                'referer'        => "http://www.google.com"
            );
        }
        $response = http_get($url, $opts, $info);

        return $info;
    }

    /**
     * @param     $domain
     * @param int $port
     *
     * @return float|int|mixed
     */
    public static function pingDomain($domain, $port = 80)
    {
        $starttime = microtime(true);
        $file = @fsockopen($domain, $port, $errno, $errstr, 10);
        $stoptime = microtime(true);
        $status = 0;

        if (!$file) {
            $status = -1;
        } else {
            fclose($file);
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }

        return $status;
    }

    /**
     *
     * check to see if someone has reciprocated a link exchange
     *
     * @param   string $remote_url the URL to look at
     * @param   string $your_link  the URL of your site
     *
     * @return  bool
     */
    public static function checkBackLink($remote_url, $your_link)
    {
        $match_pattern = preg_quote(rtrim($your_link, "/"), "/");
        $found = false;
        if ($handle = @fopen($remote_url, "r")) {
            while (!feof($handle)) {
                $part = fread($handle, 1024);
                if (preg_match("/<a(.*)href=[\"']" . $match_pattern . "(\/?)[\"'](.*)>(.*)<\/a>/", $part)) {
                    $found = true;
                    break;
                }
            }
            fclose($handle);
        }

        return $found;
    }

    /**
     *
     * @param string $ip
     *
     * @return string
     */
    public static function get_whois_abuse($ip)
    {
        if (!preg_match("/(\d{1,3}\.){3}\d{1,3}/", $ip)) {
            return false;
        }

        if (preg_match("/(abuse@[\S]+)/i", `whois {$ip}`, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }


    /**
     *
     * Function to detect user's language setting
     *
     * @param   string $default default language setting
     *
     * @return  string  the language setting
     */
    public static function detect_lang($default = "en")
    {

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        } else {
            // if we've made it this far, just return the default
            return $default;
        }
    }

    /**
     *
     *
     * gets the full URL of the current page
     *
     * @return    string
     */
    public static function getFullSelf()
    {
        /*         * * check for https ** */
        $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';

        /*         * * return the full address ** */

        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     *
     * gets the domain of the current page
     *
     * @return  string
     */
    public static function getThisDomain()
    {
        $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';

        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * redirects users to 'www' version of pages
     *
     * @param $domain
     *
     * @return    void
     */
    public static function sendtoWWW($domain)
    {
        $url = getFullSelf();
        $parts = parse_url($url);
        if (strtolower($parts['host']) == strtolower($domain)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("location:https://www.$domain" . $parts['path']);
            exit;
        }
    }

    /**
     *
     * @param string $botsFile
     * @param        $wwwdomain
     *
     * @return mixed
     */
    public static function sendBotsToWWW($botsFile, $wwwdomain)
    {
        // sanity check. if we're already on www, no need to do anything
        if ($_SERVER['HTTP_HOST'] == $wwwdomain) {
            return false;
        }

        $bots = Files::filegetcontents($botsFile, 'array');
        // array (from file) of bot UA strings

        if (false === $bots) {
            return false;
        }
        // no bots? no need to go further
        $ua = $_SERVER['HTTP_USER_AGENT'];
        // user agent string of requesting user

        if (in_array($ua, $bots)) {
            $newLocation = "http://" . $wwwdomain . $_SERVER['REQUEST_URI'];
            header("Location:$newLocation");
            exit;
        }
    }

    /**
     * parses an URL to get the host.
     *
     * @param    string $Address      URL to parse
     * @param    bool   $returnScheme whether or not to include the protocol scheme with the host
     *
     * @return   string
     */
    public static function getHost($Address, $returnScheme = true)
    {
        $parseUrl = parse_url(trim($Address));
        $host = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
        if ($returnScheme == true) {
            $host = $parseUrl['scheme'] . "://" . $host;
        }
    }

    /**
     * regex check to see if IP address is valid
     *
     * @param   string $ip the IP address we're validating
     *
     * @return  bool
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
     * @name            AddToQuery
     * @desc      Returns the a string that is either
     *            $uri if you pass it or the current
     *            uri with the variable name $varName
     *            equal to the value urlencode($varVal)
     *            It replaces a current value if it find
     *            it or adds the variable and value pair
     *            if they are new.
     * @author    corey at eyewantmedia dot com on PHP.net
     *
     * @param    string $varName
     * @param    string $varVal
     * @param    string $uri
     *
     * @return    string
     */
    public static function AddToQuery($varName, $varVal, $uri = null)
    {
        $result = '';
        $beginning = '';
        $ending = '';

        if (is_null($uri)) { //Piece together uri string
            $beginning = $_SERVER['PHP_SELF'];
            $ending = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';
        } else {
            $qstart = strpos($uri, '?');
            if ($qstart == false) {
                $beginning = $uri;
                //$ending is '' anyway
            } else {
                $beginning = substr($uri, 0, $qstart);
                $ending = substr($uri, $qstart);
            }
        }

        if (strlen($ending) > 0) {
            $vals = array();
            $ending = str_replace('?', '', $ending);
            parse_str($ending, $vals);
            $vals[$varName] = $varVal;
            $ending = '';
            $count = 0;
            foreach ($vals as $k => $v) {
                if ($count > 0) {
                    $ending .= '&amp;';
                } else {
                    $count++;
                }
                $ending .= "$k=" . urlencode($v);
            }
        } else {
            $ending = $varName . '=' . urlencode($varVal);
        }

        $result = $beginning . '?' . $ending;

        return $result;
    }

    /**
     *
     * Edit the Query portion of an url
     *
     * @author    greg dot linton at gmail dot com
     *
     * @param    string $action ethier a "+" or a "-" depending on what action you want to perform
     * @param    mixed  $var    array (+) or string (-)
     * @param    string $uri    the URL to use. if this is left out, it uses $current_url
     *
     * @return    string
     * EXAMPLE $url = ChangeQuery("-","sort", $url); removes the "sort" variable from the URL string
     */
    public static function ChangeQuery($action, $var = null, $uri = null)
    {

        if (($action == "+" && !is_array($var)) || ($action == "-" && $var == "") || $var == null) {
            return false;
        }
        if (is_null($uri)) { //Piece together uri string
            $beginning = $_SERVER['PHP_SELF'];
            $ending = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';
        } else {
            $qstart = strpos($uri, '?');
            if ($qstart == false) {
                $beginning = $uri;
                //$ending is '' anyway
            } else {
                $beginning = substr($uri, 0, $qstart);
                $ending = substr($uri, $qstart);
            }
        }

        if (strlen($ending) > 0) {
            $vals = array();
            $ending = str_replace('?', '', $ending);
            parse_str($ending, $vals);
            if ($action == "+") {
                $vals[$var[0]] = $var[1];
            }
            $ending = '';
            $count = 0;
            foreach ($vals as $k => $v) {
                if ($action == "-" && $k == $var) {
                    continue;
                }

                if ($count > 0) {
                    $ending .= '&';
                } else {
                    $count++;
                }
                $ending .= "$k=" . urlencode($v);
            }
        } else {
            $ending = $varName . '=' . urlencode($varVal);
        }

        $result = $beginning . '?' . $ending;

        return $result;
    }

    /**
     *
     * @param   string $location
     */
    public static function mobileRedirect($location)
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
            header("Location: $location");
    }

    /**
     *
     * @param string $wwwdomain
     */
    public static function forceHTTPS($wwwdomain)
    {
        if (($_SERVER['HTTP_HOST'] == $wwwdomain) && ($_SERVER["HTTPS"] != "on")) {
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
            exit();
        }
    }

    /**
     *
     * @param   string $url
     *
     * @return  string
     */
    public static function expandShortUrl($url)
    {
        stream_context_set_default(array('http' => array('method' => 'HEAD')));

        $headers = get_headers($url, 1);

        $loc = $headers['Location'];
        if (is_array($loc)) {
            // get the highest numeric index
            return $loc[max(array_keys($loc))];
        } else {
            return $loc;
        }
    }

    /**
     *
     * @param   int $type
     *
     * @return  array
     */
    public static function errorDocument($type)
    {
        $output = array();
        switch ($type) {
            case 400 :
                $output['page_title'] = 'HTTP 400 - Bad Request';
                $output['page_content'] = _('<p>Sorry, but the request cannot be fulfilled. Please try again.</p>');
                break;

            case 401 :
                $output['page_title'] = 'HTTP 401 - Unauthorized';
                $output['page_content'] = _('<p>You are not authorized to access that resource.</p>');
                break;

            case 403 :
                $output['page_title'] = 'HTTP 403 - Forbidden';
                $output['page_content'] = _('<p>You are forbidden from accessing that resource.</p>');
                break;

            case 404 :
                $output['page_title'] = 'HTTP 404 - File Not Found';
                $output['page_content'] = _('<p>The requested file was not found. Please check the URL and try again.</p>');
                break;

            case 500 :
                $output['page_title'] = 'HTTP 500 - Internal Server Error';
                $output['page_content'] = _("<p>We're sorry. We're currently experiencing server problems and cannot process your request. Please try again later.</p>");
                break;

            default :
                $output['page_title'] = 'Error';
                $output['page_content'] = _('<p>An unspecified error has occured. Please try again</p>');
                break;
        }

        return $output;
    }

    /**
     *
     * Sends a plaintext e-mail with headers predefined
     *
     * @param   string $recipient   email address of message recipient
     * @param   string $subject     subject of the email message
     * @param   string $message     email message body
     * @param   string $senderEmail email address of the message sender
     * @param   string $senderName  name of the message sender
     *
     * @return  bool
     */
    function gEmail($recipient, $subject, $message, $senderEmail, $senderName = '')
    {

        // get the IP of the user who initiated this e-mail
        $usersIP = Network::getip();
        if (strlen($senderName) < 2) {
            $senderName = $senderEmail;
        }

        // define the headers
        $headers = '';
        $headers .= "From: $senderName <$senderEmail>\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Reply-To: $senderName <$senderEmail>\n";
        $headers .= "Return-Path: $senderName <$senderEmail>\n";
        $headers .= "X-Sender: <$senderEmail>\n";
        $headers .= "X-Originating-IP: $usersIP \n";

        if ($senderEmail != $recipient) {
            $headers .= "X-Complaints-To: " . Network::get_whois_abuse($usersIP) . " \n";
        }

        $headers .= "Organization: " . $_SERVER['HTTP_HOST'] . " \n";
        $headers .= "Errors-To: <$senderEmail>\n";
        $headers .= "Message-ID: <" . md5(uniqid(rand())) . "." . preg_replace("/[^a-z0-9]/i", "", $_SERVER['HTTP_HOST']) . ">\n";
        $headers .= "X-Mailer: PHP v" . phpversion() . "\n";
        $headers .= "X-Priority: 3\n";

        $headers .= "Content-Type: text/plain; charset=utf-8\n";

        return mail($recipient, $subject, stripslashes(wordwrap($message, 72)), $headers);
    }


    /**
     * Retrieves the MIME type of a remote URL.
     *
     * @param      $url
     * @param bool $useCurl
     *
     * @return bool|mixed
     */
    public static function getRemoteMimeType($url, $useCurl = true)
    {

        // first, make sure we're working with an URL
        // warning, super-rudimentary URL validation here
        if ((false !== stripos($url, 'http')) || (false !== stripos($url, 'ftp'))) {

            // so here's the thing: using cURL this method would actually
            // get all the remote content in order to get the content type
            // which therefore also eats up the same amount of memory as whatever
            // the size of the remote resource. Bad times if you're getting
            // a large PDF, movie file, audio, or something like that.
            // so if $useCurl is false that means just use PHP's get_headers
            //
            // NOTE
            // using cURL is likely to be more accurate, especially because get_headers
            // follows redirects
            if (false === $useCurl) {
                $headers = get_headers($url, 1);
                if (substr($headers[0], 9, 3) == 200) {
                    return $headers['Content-Type'];
                } else {
                    return false;
                }

            } else {
                # the request
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);

                # get the content type
                return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            }
        }
    }

    /**
     * @param     $cache_filename
     * @param int $max_tl
     *
     * @throws gException
     */
    public function createTLD($cache_filename, $max_tl = 2)
    {
        $cache_folder = str_replace(basename($cache_filename), '', $cache_filename);
        if (!file_exists($cache_folder) || !is_writable($cache_folder)) {
            throw new gException($cache_folder . ' is not writable!');
        }
        // feel free to use "fsockopen()" or "curl_init()" if "fopen wrappers" are disabled or "memory_limit" is to low
        $tlds = @file('http://mxr.mozilla.org/mozilla-central/source/netwerk/dns/effective_tld_names.dat?raw=1');
        if ($tlds === false) {
            throw new gException('effective_tld_names.dat is not readable!');
        }
        $i = 0;
        // remove unnecessary lines
        foreach ($tlds as $tld) {
            $tlds[$i] = trim($tld);
            //     empty          comments           top level domains                   this is overboard
            if (!$tlds[$i] || $tld[0] == '/' || strpos($tld, '.') === false || substr_count($tld, '.') >= $max_tl) {
                unset($tlds[$i]);
            }
            $i++;
        }
        $tlds = array_values($tlds);
        file_put_contents($cache_filename, "<?php\n" . '$tlds = ' . str_replace(array(' ', "\n"), '', var_export($tlds, true)) . ";\n?" . ">");
    }

    /**
     * if (!file_exists('cache/tlds/all.txt')) {// feel free to refresh by interval
     * createTLD('cache/tlds/all.txt');
     * }
     * echo '<pre>';
     * foreach ($urls as $url) {
     * echo $url . ':' . var_export(getHost($url), true) . "\n";
     * }
     * echo $_SERVER['SERVER_NAME'] . ':' . var_export(getHost(), true) . "\n";
     * echo '</pre>';
     */
    function getHostDomain($pathToCache, $dom = '', $fast = false)
    {
        // general
        $dom = !$dom ? $_SERVER['SERVER_NAME'] : $dom;
        // for parse_url()                  ftp://           http://          https://
        $dom = !isset($dom[5]) || ($dom[3] != ':' && $dom[4] != ':' && $dom[5] != ':') ? 'http://' . $dom : $dom;
        // remove "/path/file.html", "/:80", etc.
        $dom = parse_url($dom, PHP_URL_HOST);
        // replace absolute domain name by relative (http://www.dns-sd.org/TrailingDotsInDomainNames.html)
        $dom = trim($dom, '.');
        // for fast check
        $dom = $fast ? str_replace(array('www.', 'ww.'), '', $dom) : $dom;
        // separate domain level
        $lvl = explode('.', $dom);
        // 0 => www, 1 => example, 2 => co, 3 => uk
        // fast check
        if ($fast) {
            if (!isset($lvl[2])) {
                return isset($lvl[1]) ? $dom : false;
            }
        }
        // set levels
        krsort($lvl);
        // 3 => uk, 2 => co, 1 => example, 0 => www
        $lvl = array_values($lvl);
        // 0 => uk, 1 => co, 2 => example, 3 => www
        $_1st = $lvl[0];
        $_2nd = isset($lvl[1]) ? $lvl[1] . '.' . $_1st : false;
        $_3rd = isset($lvl[2]) ? $lvl[2] . '.' . $_2nd : false;
        $_4th = isset($lvl[3]) ? $lvl[3] . '.' . $_3rd : false;
        // tld check
        require($pathToCache);
        // includes "$tlds"-Array
        $tlds = array_flip($tlds);
        // needed for isset()
        // fourth level is TLD
        if ($_4th && !isset($tlds['!' . $_4th]) && (isset($tlds[$_4th]) || isset($tlds['*.' . $_3rd]))) {
            $dom = isset($lvl[4]) ? $lvl[4] . '.' . $_4th : false;
        } // third level is TLD
        else if ($_3rd && !isset($tlds['!' . $_3rd]) && (isset($tlds[$_3rd]) || isset($tlds['*.' . $_2nd]))) {
            $dom = $_4th;
        } // second level is TLD
        else if (!isset($tlds['!' . $_2nd]) && (isset($tlds[$_2nd]) || isset($tlds['*.' . $_1st]))) {
            $dom = $_3rd;
        } // first level is TLD
        else {
            $dom = $_2nd;
        }

        return $dom ? $dom : false;
    }

    /**
     * This simple function just returns the part of a query that can be used to retrieve the domain from an url in MySQL
     */
    function sqlDomainSubstring()
    {
        return "substring_index(substring_index(substring_index(url, '/', 3), '/', -1), '.', -2)";
    }


    /**
     * gets the content from a remote URL. Meant for grabbing web page content.
     *
     * @param      $url
     * @param bool $followRedirects
     * @param bool $printInfo
     *
     * @return bool|mixed
     */
    public static function getRemoteURLContent($url, $followRedirects = false, $printInfo = false)
    {

        //open connection
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirects);

        //execute post and get results
        $result = curl_exec($ch);

        if (true === $printInfo) {
            echo 'ERROR INFO (if any): ' . curl_error($ch) . '<br>';
            echo '<h2>Curl Info </h2><pre><br>';
            print_r(curl_getinfo($ch));
            echo '</pre>';
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpcode > 302) {
            return false;
        }

        //close connection
        curl_close($ch);

        //the test results
        return $result;

    }

}
