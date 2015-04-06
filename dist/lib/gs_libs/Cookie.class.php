<?php

/**
 *
 * Static class for creation and management of cookies
 *
 * DESCRIPTION
 *
 *     Some example usage:
 *
 *      Style preference, persists only until the browser is closed
 *      Cookie::set('style', 'black_and_orange', Cookie::Session);
 *
 *      Remember the users email address to pre-fill the login form when they return
 *      Cookie::set('rememberme', 'email@domain.com', Cookie::ThirtyDays);
 *
 *      Tracking cookie that effectively lasts forever
 *      Cookie::set('tracking', 'sdfoiwuyo8who8wfhow8fhso4', Cookie::Lifetime, '/', '.domain.com');
 *
 */
class Cookie
{
    // Class constants representing common time intervals
    // for use in setting expiration dates for the cookies
    const Session = null;
    const OneHour = 3600;
    const OneDay = 86400;
    const SevenDays = 604800;
    const ThirtyDays = 2592000;
    const SixMonths = 15811200;
    const OneYear = 31536000;
    const Lifetime = -1;

    // 2030-01-01 00:00:00

    /**
     * determines if a cookie already exists with this name
     * with *any* value
     *
     * @param    string $name the name of the cookie
     *
     * @return   bool
     */
    static public function exists($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     *
     * Returns true if there is no cookie with this name or it's empty, or 0,
     * or a few other things. Check http://php.net/empty for a full list.
     *
     * @param    string $name the name of the cookie
     *
     * @return   bool
     */
    static public function isEmpty($name)
    {
        return empty($_COOKIE[$name]);
    }

    /**
     *
     * Get the value of the given cookie. If the cookie does not exist the value
     * of $default will be returned.
     *
     * @param   string $name
     * @param   string $default
     *
     * @return  string
     */
    static public function get($name, $default = '')
    {
        return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default);
    }

    /**
     * Set a cookie. Silently does nothing if headers have already been sent.
     *
     * @param        $name
     * @param        $value
     * @param int    $expiry
     * @param string $path
     * @param bool   $domain
     * @param bool   $secure
     * @param bool   $httponly
     *
     * @return bool
     */
    static public function set($name, $value, $expiry = self::OneYear, $path = '/', $domain = false, $secure = false, $httponly = true)
    {
        $retval = false;

        // sanity check: make sure headers haven't been set already
        if (false === headers_sent()) {
            // if the domain isn't set, set it to current host
            if ($domain === false) {
                $domain = $_SERVER['HTTP_HOST'];
            }

            // if there's no expiration date, set it REALLY far away
            if ($expiry === -1) {
                $expiry = 1893456000;
            } // two sanity checks
            elseif (is_numeric($expiry)) {
                $expiry += time();
            } else {
                $expiry = strtotime($expiry);
            }

            $retval = setcookie($name, $value, $expiry, $path, $domain, $secure, $httponly);

            if ($retval) {
                // this allows us to create a 'live' cookie that actually exists
                // without waiting for the page to refresh.
                // note: this doesn't mean the cookie actually exists on user's computer
                // it just forces its value in the $_COOKIE superglobal array
                $_COOKIE[$name] = $value;

                return true;
            }

            return true;
        }

        return $retval;
    }

    /**
     * @param        $name
     * @param string $path
     * @param bool   $domain
     * @param bool   $remove_from_global Set to true to remove this cookie from this request.
     *
     * @return bool
     */
    static public function delete($name, $path = '/', $domain = false, $remove_from_global = false)
    {
        $retval = false;

        // sanity check: make sure headers haven't been set already
        if (!headers_sent()) {
            if ($domain === false) {
                $domain = $_SERVER['HTTP_HOST'];
                $retval = setcookie($name, '', time() - (3600 * 25), $path, $domain);

                if ($remove_from_global) {
                    unset($_COOKIE[$name]);
                }
            }
        }

        return $retval;
    }

    /**
     * Set a cookie with multiple values. Essentially, just serializes an array
     * Neat way to set multiple values in one cookie, rather than a bunch.
     * Silently does nothing if headers have already been sent.
     *
     * @param        $name
     * @param        $array
     * @param int    $expiry
     * @param string $path
     * @param bool   $domain
     *
     * @return bool
     */
    static public function setMulti($name, $array, $expiry = self::OneYear, $path = '/', $domain = false)
    {
        $retval = false;

        // sanity check: make sure headers haven't been set already
        if (!headers_sent()) {
            if ($domain === false) {
                $domain = $_SERVER['HTTP_HOST'];
            }

            if ($expiry === -1) {
                $expiry = 1893456000;
                // Lifetime = 2030-01-01 00:00:00
            } elseif (is_numeric($expiry)) {
                $expiry += time();
            } else {
                $expiry = strtotime($expiry);
            }

            //@TODO actually this is missing a parameter declaration
            // but not sure whether this is a bug or whether the getMulti has a big
            $value = Cookie::getMulti($name);
            if ($value == null) {
                $value = array();
            } elseif (!is_array($value)) {
                $value = (array)$value;
            } else {
                foreach ($array AS $k => $v) {
                    $value[$k] = $v;
                }
            }

            $value = serialize($value);

            $retval = setcookie($name, $value, $expiry, $path, $domain);

            if ($retval) {
                $_COOKIE[$name] = $value;
            }
        }

        return $retval;
    }

    /**
     *
     * Unserializes cookie data, set by setMulti, above.
     * If the cookie does not exist the value
     * of $default will be returned.
     *
     * @param   string $name
     * @param   string $default
     *
     * @return  mixed
     */
    static public function getAllMulti($name, $default = '')
    {
        if (isset($_COOKIE[$name])) {
            $data = $_COOKIE[$name];
            if ($data != '') {
                return unserialize($data);
            } else {
                return array();
            }
        } else {
            return $default;
        }
    }

    /**
     * Get the value of an individual item in a cookie set by setMulti.
     * If the cookie does not exist the value
     * of $default will be returned.
     *
     * @param   string $name
     * @param   string $key
     * @param   string $default
     *
     * @return  mixed
     */
    static public function getMulti($name, $key, $default = '')
    {
        $data = Cookie::getAllMulti($name);
        if ($data != null && isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }
}
