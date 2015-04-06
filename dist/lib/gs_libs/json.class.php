<?php

/**
 * Simple class used to encode & decode JSON with PHP.
 */
class json
{

    /**
     * class constructor
     */
    public function __construct()
    {
        // no idea yet WTH to do

    }

    /**
     * Decoding method. Replaces the native function to be a bit more robust
     *
     * @param   string $json    the string of JSON to be decoded
     * @param   bool   $assoc   whether or not to return an associative array (FALSE means return an object)
     * @param   int    $depth   how many recursions
     * @param   mixed  $options options to pass to the native json_decode
     *
     * @return  bool
     */
    public function jsonDecode($json, $assoc = true, $depth = 512, $options = 0)
    {
        try {
            // trim off any white space at the beginning or end of the JSON string
            $json = trim($json);

            // empty JSON or boolean FALSE is bad and you should feel bad
            if ((false == $json) || (empty($json))) {
                throw new gException("JSON was empty");
            }

            // SO FAR SO GOOD: LETS SUBJECT IT TO A BUNCH OF STRING MANIPULATION SHIT

            // force encode it as UTF-8
            $json = json::remove_utf8_bom(utf8_encode($json));

            // search and remove comments like /* */ and //
            $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);

            // strip linebreaks
            $json = str_replace(array("\n", "\r"), "", $json);

            //remove padding
            $json = preg_replace('/.+?({.+}).+/', '$1', $json);

            // destroy a trailing comma
            $json = preg_replace('/(,)\s*}$/', '}', $json);

            // Oh yeah, let's decode dat motherfucker
            //$decoded = json_decode($json, $assoc, $depth, $options);
            $decoded = json_decode($json, $assoc, $depth);

            // success case: we successfully decoded it and had no errors
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } // fuck. We have errors and now we gotta figure out WTF went wrong.
            else {
                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH :
                        $msg = ' - Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH :
                        $msg = ' - Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR :
                        $msg = ' - Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX :
                        $msg = ' - Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8 :
                        $msg = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default :
                        $msg = ' - Unknown error';
                        break;
                }
                throw new gException("JSON Decoding Failed" . $msg);
            }
        } catch (gException $e) {
            // @TODO fix this.  Echoing out exception messages is dumb
            echo $e->getMessage();

            return false;
        }
    }

    /**
     * Decode method.  Basically just a tad more robust handling of the to-be-encoded data
     *
     * @param   mixed $json    the stuff to encode. Typically an array
     * @param   mixed $options options to pass to the native function
     * @param   int   $depth   how far to recurse
     * @param   string| bool
     *
     * @return string
     */
    public function jsonEncode($json, $options = 0, $depth = 512)
    {

        //force the content to utf8 as a sanity check
        $json = json::utf8_encode_recursive($json);

        return json_encode($json, $options, $depth);
    }

    /**
     * Encodes information in UTF-8, recursively (for arrays and objects)
     *
     * @param   mixed $data
     *
     * @return  mixed   the data, encoded
     */
    public static function utf8_encode_recursive($data)
    {
        if (is_string($data)) {
            return utf8_encode($data);
        }
        if (is_object($data)) {
            $ovs = get_object_vars($data);
            $new = $data;
            foreach ($ovs as $k => $v) {
                $new->$k = json::utf8_encode_recursive($new->$k);
            }

            return $new;
        }

        if (!is_array($data)) {
            return $data;
        }
        $ret = array();
        foreach ($data as $i => $d) {
            $ret[$i] = json::utf8_encode_recursive($d);
        }

        return $ret;
    }

    /**
     * Removes stupid BOM character from string
     *
     * @param   string $text the text to strip the BOM from
     *
     * @return  string
     */
    public static function remove_utf8_bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);

        return $text;
    }

}