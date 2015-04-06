<?php

/**
 *
 * This class is meant to provide a way to do some basic security check
 * so that the only variables passed through global means are those which
 * we want.
 *
 * It might seem a pain-in-the-ass to do this, but the reality is, if you don't
 * know where your data is coming from, then you're probably at risk for all
 * kinds of nasty issues down the road - especially from a security standpoint.
 *
 * USAGE EXAMPLE
 *    // The array of keys we expect
 *    $expected = array('one', 'two', 'three', 'four');
 *
 *    // Instantiate a new cleanGlobals object
 *    $cleanGlobals = new cleanGlobals();
 *
 *    // clean the $_GET array, keeping only those
 *    // keys we expected to receive.
 *    $cleanGlobals->cleanGet($expected);
 *
 *    //Alternately, you can call the method statically (this is the preferred approach):
 *    cleanGlobals::cleanGet($expected);
 *    // The class methods are declared as public to facilitate either approach
 *
 *    End result: Given the array 'one', 'two', 'three', 'four'
 *    If the $_GET array had also received keys of 'foo' and 'bar'
 *    those keys (and their values, obviously) would be stripped from the array
 *
 *
 *    ***CAUTION!!!!***
 *    The power of this class is also a potential source of bugs!
 *    These methods WILL unset any key which is not in your 'expected' array!
 *  This means you may chase your tail wondering why a superglobal isn't showing up
 *
 *    Also note: While this certainly enhances security, you must still
 *    validate, filter, and escape input as normal to ensure security.
 *
 */
class cleanGlobals
{

    /**
     * $_SERVER: Server and execution environment information
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanServer($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_SERVER as $key => $val) {
            if (in_array($key, $expected)) {
                $_SERVER[$key] = self::smarTrim($val);
            } else {
                unset($_SERVER[$key]);
            }
        }

        return true;
    }

    /**
     * $_GET: HTTP GET variables
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanGet($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_GET as $key => $val) {
            if (in_array($key, $expected)) {
                $_GET[$key] = self::smarTrim($val);
            } else {
                unset($_GET[$key]);
            }
        }

        return true;
    }

    /**
     * $_POST: HTTP POST variables
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanPost($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_POST as $key => $val) {
            if (in_array($key, $expected)) {
                $_POST[$key] = self::smarTrim($val);
            } else {
                unset($_POST[$key]);
            }
        }

        return true;
    }

    /**
     * $_FILES: HTTP File Upload variables
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanFiles($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_FILES as $key => $val) {
            if (in_array($key, $expected)) {
                $_FILES[$key] = $val;
            } else {
                unset($_FILES[$key]);
            }
        }

        return true;
    }

    /**
     * $_REQUEST: HTTP Request variables
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanRequest($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_REQUEST as $key => $val) {
            if (in_array($key, $expected)) {
                $_REQUEST[$key] = self::smarTrim($val);
            } else {
                unset($_REQUEST[$key]);
            }
        }

        return true;
    }

    /**
     * $_SESSION: Session variables
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanSession($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_SESSION as $key => $val) {
            if (in_array($key, $expected)) {
                $_SESSION[$key] = self::smarTrim($val);
            } else {
                unset($_SESSION[$key]);
            }
        }

        return true;
    }

    /**
     * $_ENV: Environment variables
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanEnv($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_ENV as $key => $val) {
            if (in_array($key, $expected)) {
                $_ENV[$key] = self::smarTrim($val);
            } else {
                unset($_GET[$key]);
            }
        }

        return true;
    }

    /**
     * $_COOKIE: HTTP Cookies
     *
     * @param $expected
     *
     * @return bool
     */
    public function cleanCookie($expected)
    {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($_COOKIE as $key => $val) {
            if (in_array($key, $expected)) {
                $_COOKIE[$key] = self::smarTrim($val);
            } else {
                unset($_COOKIE[$key]);
            }
        }

        return true;
    }

    /**
     *
     * @param   string| array $val
     *
     * @return  string| array
     */
    public function smarTrim($val)
    {
        if (!is_array($val)) {
            return trim($val);
        } else {
            return $val;
        }
    }

    /**
     *
     * utility function to dump out superglobals so we can see what's going on
     * using the type of 'ALL' will dump all superglobals. Otherwise a comma-delimited
     * list of which superglobals to dump can be used.  For instance 'cookie,session' would
     * dump all cookie and session superglobals.
     *
     * @param   string $type which superglobals to dump
     *
     * @return  void
     */
    public function dumpGlobals($type = 'ALL')
    {

        // dump em all
        if ($type == 'ALL') {
            echo '<h2>$GLOBALS</h2>';
            echo '<pre>';
            var_dump($GLOBALS);
            echo '</pre>';
            echo '<hr>';

            return;
        } else {
            $type = strtolower($type);

            $tArray = array_map('trim', explode(',', $type));

            // dump cookies
            if (in_array('cookie', $tArray)) {
                echo '<h2>$_COOKIE</h2>';
                echo '<pre>';
                var_dump($_COOKIE);
                echo '</pre>';
                echo '<hr>';
            }

            //dump env
            if (in_array('env', $tArray)) {
                echo '<h2>$_ENV</h2>';
                echo '<pre>';
                var_dump($_ENV);
                echo '</pre>';
                echo '<hr>';
            }

            //dump files
            if (in_array('files', $tArray)) {
                echo '<h2>$_FILES</h2>';
                echo '<pre>';
                var_dump($_FILES);
                echo '</pre>';
                echo '<hr>';
            }

            //dump get
            if (in_array('get', $tArray)) {
                echo '<h2>$_GET</h2>';
                echo '<pre>';
                var_dump($_GET);
                echo '</pre>';
                echo '<hr>';
            }

            //dump post
            if (in_array('post', $tArray)) {
                echo '<h2>$_POST</h2>';
                echo '<pre>';
                var_dump($_POST);
                echo '</pre>';
                echo '<hr>';
            }

            //dump request
            if (in_array('request', $tArray)) {
                echo '<h2>$_REQUEST</h2>';
                echo '<pre>';
                var_dump($_REQUEST);
                echo '</pre>';
                echo '<hr>';
            }

            //dump server
            if (in_array('server', $tArray)) {
                echo '<h2>$_SERVER</h2>';
                echo '<pre>';
                var_dump($_SERVER);
                echo '</pre>';
                echo '<hr>';
            }

            //dump session
            if (in_array('session', $tArray)) {
                echo '<h2>$_SESSION</h2>';
                echo '<pre>';
                var_dump($_SESSION);
                echo '</pre>';
                echo '<hr>';
            }

            return;
        }
    }

}
