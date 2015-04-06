<?php

/**
 *
 *      A PHP library of helpful string manipulation methods.  This class does
 *      not need to be assigned to an object, as you could use the :: access method,
 *      such as Strings::isString to access them statically
 */
class Strings
{

    /**
     * Validates whether the given element is a string.
     *
     * @return bool
     *
     * @param  string $element
     * @param  bool   $require_content If the string can be empty or not
     */
    public static function isString($element, $require_content = true)
    {
        return (!is_string($element)) ? false : ($require_content && $element == '' ? false : true);
    }

    /**
     * Converts all strings, or all elements of an array, no matter how nested
     * the array is, to html entities.
     *
     * @return string|array
     *
     * @param  string|array $element
     */
    public static function convertTextToHTML($element)
    {
        return Strings::processFunction($element, 'htmlentities');
    }

    /**
     * Removes the first or last word from a string.
     *
     * @return string
     *
     * @param  string $string
     * @param  bool   $start whether to trim at start (true) or end (false) of string
     */
    public static function trimWordFromString($string, $start = true)
    {
        if (Strings::isString($string)) {
            $trimmed = trim($string);
            if (!substr_count($trimmed, ' ')) {
                return $trimmed;
            } else {
                return ($start) ? substr($trimmed, strpos($trimmed, ' ') + 1, strlen($trimmed)) : substr($trimmed, 0, strrpos($trimmed, ' '));
            }
        }

        return false;
    }

    /**
     * Removes the first word from a string.
     *
     * @return string
     *
     * @param  string $string
     *
     * @see   trimWordFromString()
     */
    public static function trimFirstWordFromString($string)
    {
        return Strings::trimWordFromString($string, true);
    }

    /**
     * Removes the last word from a string.
     *
     * @return string
     *
     * @param string $string
     *
     * @see   trimWordFromString()
     */
    public static function trimLastWordFromString($string)
    {
        return Strings::trimWordFromString($string, false);
    }


    /**
     * @param $string
     *
     * @return array|bool|string
     */
    public static function trimString($string)
    {
        if (Strings::isString($string)) {
            return Strings::processFunction($string, 'trim');
        }

        return false;
    }

    /**
     * Can left-trim a string, or all elements of an array, no matter
     * how nested the array is.
     *
     * @param $string
     *
     * @return array|bool|string
     */
    public static function trimStringLeft($string)
    {
        if (Strings::isString($string)) {
            return Strings::processFunction($string, 'ltrim');
        }

        return false;
    }

    /**
     * Can right-trim a string, or all elements of an array, no matter
     * how nested the array is.
     *
     * @param $string
     *
     * @return array|bool|string
     */
    public static function trimStringRight($string)
    {
        if (Strings::isString($string)) {
            return Strings::processFunction($string, 'rtrim');
        }

        return false;
    }

    /**
     * Adds slashes to a string, or all elements of an array, no matter
     * how nested the array is.
     *
     * If the 'check_gpc' parameter is true then slashes will be applied
     * depending on magic_quotes setting.
     *
     * @return string|array
     *
     * @param  string|array $element
     * @param  bool         $check_gpc
     */
    public static function addSlashesToString($element, $check_gpc = true)
    {
        return ($check_gpc && get_magic_quotes_gpc()) ? $element : Strings::processFunction($element, 'addslashes');
    }

    /**
     * Removes slashes from a string, or all elements of an array, no matter
     * how nested the array is.
     *
     * If the 'check_gpc' parameter is true then slashes will be removed
     * depending on magic_quotes setting.
     *
     * @return string|array
     *
     * @param  string|array $element
     * @param  bool         $check_gpc
     */
    public static function trimSlashesFromString($element, $check_gpc = true)
    {
        return ($check_gpc && !get_magic_quotes_gpc()) ? $element : Strings::processFunction($element, 'stripslashes');
    }

    /**
     * Performs the passed function recursively.
     *
     * @return string|array
     *
     * @param  string|array $element
     * @param  string       $function
     */
    public static function processFunction($element, $function)
    {
        if (function_exists($function) === true) {
            return $function($element);
        }

        return false;
    }

    /**
     * Get the ordinal value of a number (1st, 2nd, 3rd, 4th).
     *
     * @return string
     *
     * @param  int $value
     */
    public static function getOrdinalString($value)
    {
        static $ords = array('th', 'st', 'nd', 'rd');
        if ((($value %= 100) > 9 && $value < 20) || ($value %= 10) > 3) {
            $value = 0;
        }

        return $ords[$value];
    }

    /**
     * Returns the plural appendage, handy for instances like: 1 file,
     * 5 files, 1 box, 3 boxes.
     *
     * @return string
     *
     * @param  int    $value
     * @param  string $append what value to append to the string
     */
    public static function getPluralString($value, $append = 's')
    {
        return ($value == 1 ? '' : $append);
    }

    /**
     * @param $word
     *
     * @return bool|mixed
     */
    public static function pluralize($word)
    {
        $plural = array(
            '/(quiz)$/i'               => '1zes',
            '/^(ox)$/i'                => '1en',
            '/([m|l])ouse$/i'          => '1ice',
            '/(matr|vert|ind)ix|ex$/i' => '1ices',
            '/(x|ch|ss|sh)$/i'         => '1es',
            '/([^aeiouy]|qu)ies$/i'    => '1y',
            '/([^aeiouy]|qu)y$/i'      => '1ies',
            '/(hive)$/i'               => '1s',
            '/(?:([^f])fe|([lr])f)$/i' => '12ves',
            '/sis$/i'                  => 'ses',
            '/([ti])um$/i'             => '1a',
            '/(buffal|tomat)o$/i'      => '1oes',
            '/(bu)s$/i'                => '1ses',
            '/(alias|status)/i'        => '1es',
            '/(octop|vir)us$/i'        => '1i',
            '/(ax|test)is$/i'          => '1es',
            '/s$/i'                    => 's',
            '/$/'                      => 's');

        $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

        $irregular = array(
            'person' => 'people',
            'man'    => 'men',
            'child'  => 'children',
            'sex'    => 'sexes',
            'move'   => 'moves');

        $lowercased_word = strtolower($word);

        foreach ($uncountable as $_uncountable) {
            if (substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable) {
                return $word;
            }
        }

        foreach ($irregular as $_plural => $_singular) {
            if (preg_match('/(' . $_plural . ')$/i', $word, $arr)) {
                return preg_replace('/(' . $_plural . ')$/i', substr($arr[0], 0, 1) . substr($_singular, 1), $word);
            }
        }

        foreach ($plural as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return false;

    }


    /**
     * Strips all newline characters (\n) from a string
     *
     * @return string
     *
     * @param  string
     */
    public static function trimNewlinesFromString($string)
    {
        if (Strings::isString($string)) {
            return str_replace("\n", '', $string);
        }

        return false;
    }

    /**
     * Strips all carriage return characters (\r) from a string
     *
     * @param $string
     *
     * @return string
     */
    public static function trimCarriageReturnsFromString($string)
    {
        if (Strings::isString($string)) {
            return str_replace("\r", '', $string);
        }

        return false;
    }

    /**
     * Counts number of words in a string.
     *
     * if $real_words == true then remove things like '-', '+', that
     * are surrounded with white space.
     *
     * @return string|null
     *
     * @param  string $string
     * @param  bool   $real_words
     */
    public static function countWords($string, $real_words = true)
    {
        if (Strings::isString($string)) {
            if ($real_words == true) {
                $string = preg_replace('/(\s+)[^a-zA-Z0-9](\s+)/', ' ', $string);
            }

            return (count(split('[[:space:]]+', $string)));
        }

        return false;

    }

    /**
     * Counts number of sentences in a string.
     *
     * @return string|null
     *
     * @param  string $string
     */
    public static function countSentences($string)
    {
        if (Strings::isString($string)) {
            return preg_match_all('/[^\s]\.(?!\w)/', $string, $matches);
        }

        return false;

    }

    /**
     * Counts number of paragraphs in a string.
     *
     * @return string|null
     *
     * @param  string $string
     */
    public static function countParagraphs($string)
    {
        if (Strings::isString($string)) {
            $string = str_replace("\r", "\n", $string);

            return count(preg_split('/[\n]+/', $string));
        }

        return false;

    }

    /**
     *
     * @param string $string
     *
     * @return int
     */
    public static function countLines($string)
    {
        if (Strings::isString($string)) {
            $array = explode("\n", $string);
            $array = Arrays::arrayRemoveEmpty($array);

            return count($array);
        }

        return false;
    }

    /**
     * Gather information about a passed string.
     *
     * If $real_words == true then remove things like '-', '+', that are
     * surrounded with white space.
     *
     * @return string|null
     *
     * @param  string $string
     * @param  bool   $real_words
     */
    public static function getStringInformation($string, $real_words = true)
    {
        if (Strings::isString($string)) {
            $info = array();
            $info['character'] = ($real_words) ? preg_match_all('/[^\s]/', $string, $matches) : strlen($string);
            $info['word'] = Strings::countWords($string, $real_words);
            $info['sentence'] = Strings::countSentences($string);
            $info['paragraph'] = Strings::countParagraphs($string);

            return $info;
        }

        return false;
    }

    /**
     *
     * @desc    abusive language filter, replaces bad words in a string with '****'
     *
     * @param    string $string string of text
     * @param   array   $swear  the array of swear words to eliminate
     *
     * @return    string    the same input string is redisplayed with the curse words removed
     */
    public function abusiveLanguage($string, $swear)
    {
        if (Strings::isString($string)) {
            foreach ($swear as $value) {
                $string = str_replace($value, "****", $string);
            }

            return $string;
        }

        return false;
    }

    /**
     *
     * formats plain text to be more readable
     *
     * @param   string $string string of text
     * @param   bool   $add_links
     *
     * @return  string    the same input string is redisplayed with new formatting
     */
    public static function plainTextDisplay($string, $add_links = true)
    {
        if (Strings::isString($string)) {
            $string = strip_tags($string);
            // strip out HTML
            $string = nl2br($string); // add break tags in place of new lines
            if ($add_links == true) {
                $string = HTML::addLinks($string); // add links where needed
                $string = HTML::email_links($string); // add e-mail addresses where needed
            }

            return $string;
        }

        return false;
    }

    /**
     * @param $codes
     *
     * @return string
     */
    public static function uchr($codes)
    {
        if (is_scalar($codes))
            $codes = func_get_args();
        $str = '';
        foreach ($codes as $code)
            $str .= html_entity_decode('&#' . $code . ';', ENT_NOQUOTES, 'UTF-8');

        return $str;
    }

    /**
     *
     * function will encrypt text. Used on passwords both functions for encrypt and decrypt found elsewhere
     * props to whoever wrote these (I forgot, sorry)
     *
     * @param    string $string the string of text to be encrypted
     * @param    string $key    the encryption key
     *
     * @return    string    the input string is redisplayed in an encrypted form
     */
    public static function uEnCrypt($string, $key)
    {
        if (Strings::isString($string)) {
            $in = iconv_get_encoding("input_encoding");

            $string = iconv($in, 'UTF-8', $string);

            $result = '';
            for ($i = 1; $i <= strlen($string); $i++) {
                $char = substr($string, $i - 1, 1);
                $keychar = substr($key, ($i % strlen($key)) - 1, 1);
                $char = Strings::uchr(ord($char) + ord($keychar));
                $result .= $char;
            }

            return $result;
        }

        return false;

    }

    /**
     *
     * function will decrypt text that has been encrypted with the above function
     * must be decrypted using the same $key as the one used to encrypt it
     *
     * @param    string $string the string of text to be decrypted
     * @param    string $key    the encryption key
     *
     * @return    string    the input string (which has been ENcrypted using the function above
     *                        is redisplayed in DEcrypted format
     */
    public static function uDeCrypt($string, $key)
    {
        if (Strings::isString($string)) {
            $result = '';
            for ($i = 1; $i <= strlen($string); $i++) {
                $char = substr($string, $i - 1, 1);
                $keychar = substr($key, ($i % strlen($key)) - 1, 1);
                $char = Strings::uchr(ord($char) - ord($keychar));
                $result .= $char;
            }

            return $result;
        }

        return false;
    }

    /**
     *
     * function will encrypt text. Used on passwords both functions for encrypt and decrypt found elsewhere
     * props to whoever wrote these (I forgot, sorry)
     *
     * @param    string $string the string of text to be encrypted
     * @param    string $key    the encryption key
     *
     * @return    string    the input string is redisplayed in an encrypted form
     */
    public static function enCrypt($string, $key)
    {
        if (Strings::isString($string)) {
            $result = '';
            for ($i = 1; $i <= strlen($string); $i++) {
                $char = substr($string, $i - 1, 1);
                $keychar = substr($key, ($i % strlen($key)) - 1, 1);
                $char = chr(ord($char) + ord($keychar));
                $result .= $char;
            }

            return $result;
        }

        return false;
    }

    /**
     *
     * function will decrypt text that has been encrypted with the above function
     * must be decrypted using the same $key as the one used to encrypt it
     *
     * @param    string $string the string of text to be decrypted
     * @param    string $key    the encryption key
     *
     * @return    string    the input string (which has been ENcrypted using the function above
     *                        is redisplayed in DEcrypted format
     */
    public static function deCrypt($string, $key)
    {
        if (Strings::isString($string)) {
            $result = '';
            for ($i = 1; $i <= strlen($string); $i++) {
                $char = substr($string, $i - 1, 1);
                $keychar = substr($key, ($i % strlen($key)) - 1, 1);
                $char = chr(ord($char) - ord($keychar));
                $result .= $char;
            }

            return $result;
        }

        return false;
    }

    /**
     *
     * function to generate random strings
     *
     * @param        int $length number of characters in the generated string
     *
     * @return        string    a new string is created with random characters of the desired length
     */
    public static function randomString($length = 32)
    {
        $randstr = '';
        srand((double)microtime(true) * 1000000);
        //our array add all letters and numbers if you wish
        $chars = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5',
            '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
            'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        for ($rand = 0; $rand <= $length; $rand++) {
            $random = rand(0, count($chars) - 1);
            $randstr .= $chars[$random];
        }

        return $randstr;
    }

    /**
     * Generates a BLOWFISH encrypted string
     *
     * @Todo test this. May not be working
     *
     * @param   $string
     *
     * @return  string
     */
    public static function bCrypt($string)
    {
        if (Strings::isString($string)) {
            return crypt($string, '$2a$16$' . Strings::randomString(22));
        }

        return false;
    }

    /**
     *
     * takes a string of text and obfuscates all but the last 4 digits.
     * perfect for credit cards and SSNs
     *
     * @param        string $string the string of text being obfuscated
     *
     * @return        string
     */
    public static function obfuscateString($string)
    {
        if (Strings::isString($string)) {
            return str_pad(substr($string, -4), strlen($string), 'x', STR_PAD_LEFT);
        }

        return false;

    }

    /**
     *
     * takes two names and concatenates it as
     * first letter of the first name, full last name
     * such as 'kgroves' out of "Karl" and "Groves"
     *
     * @param $first
     * @param $last
     *
     * @return        string
     */
    public static function concatString($first, $last)
    {
        $first = strtoupper($first);
        $first = strtolower($first);

        $first_initial = $first{0};

        $last = strtoupper($last);
        $last = strtolower($last);

        $concat = $first_initial;
        $concat .= $last;

        return $concat;
    }

    /**
     *
     * makes a long string of text shorter
     *
     * @param    string $string    the text being shortened
     * @param    int    $length    the length of the new string
     * @param    bool   $use_title whether or not to put the full text in a title attribute
     * @param   string  $class     what class to apply
     *
     * @return    string    the shortened string of text
     */
    public static function truncateString($string, $length, $use_title = false, $class = '')
    {
        if (Strings::isString($string)) {
            $output = '';

            if (strlen($string) > $length) {
                $output .= substr($string, 0, $length);
                $output .= "...";
                if ($use_title == true) {
                    $output = "<span title=\"$string\" $class>$output</span>";
                }
            } else {
                $output = $string;
            }

            return $output;
        }

        return false;

    }

    /**
     *
     * does a fancy truncate on a link so it doesn't b0rk anything
     * i.e. http://www.example.com/reallylonglinkishere.html
     * becomes something like
     * http://www.example.com/r...here.html
     *
     * @param    string $link  the link
     * @param    int    $limit the number of characters allowed
     *
     * @return    string    the same input string is returned, shortened
     */
    public static function trimLink($link, $limit = 50)
    {
        if ($limit > 39) {
            //  we test the limit that it's at least 39 characters,
            //  otherwise our presentation will look awkward!
            //// figure out the total length of the link title
            if (strlen($link) > $limit) {
                // edit the link
                // we also return the last 10 characters of the long
                // URL i.e. the '-10' value in the line below
                $link = substr($link, 0, ($limit / 2)) . '...' .
                    substr($link, -10);
            }
        }

        // return the processed string
        return $link;
    }


    /**
     * similar to above but preferred due to robustness
     *
     * @param   string $string    the string to truncate
     * @param   int    $maxLength the maximum final length of the string
     * @param   string $separator the separator in the middle of the truncated string
     *
     * @return  string
     */
    public static function truncateToMiddle($string, $maxLength, $separator = '/.../')
    {
        if (Strings::isString($string)) {
            $separatorLength = strlen($separator);
            $maxLength = $maxLength - $separatorLength;
            $start = $maxLength / 2;
            $trunc = strlen($string) - $maxLength;

            return substr_replace($string, $separator, $start, $trunc);
        }

        return false;
    }

    /**
     *
     * exactly the opposite of PHP's nl2br, this function
     * takes <br> elements and turns them into \n
     *
     * @param    string $string the string of text to be modified
     *
     * @return    string
     */
    public static function br2nl($string)
    {
        if (Strings::isString($string)) {
            return preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $string);
        }

        return false;

    }

    /**
     * @name                bin2text
     * @desc        convert binary string to text
     *
     * @param        string $string
     *
     * @return        string    the newly converted text
     */
    public static function bin2text($string)
    {
        if (Strings::isString($string)) {
            $text_str = '';
            $chars = explode("\n", chunk_split(str_replace("\n", '', $string), 8));
            $_I = count($chars);
            for ($i = 0; $i < $_I; $text_str .= chr(bindec($chars[$i])), $i++) ;

            return $text_str;
        }

        return false;

    }

    /**
     *
     * convert a string to binary
     *
     * @param    string $string
     *
     * @return    string    the new binary string
     */
    public static function text2bin($string)
    {
        if (Strings::isString($string)) {
            $len = strlen($string);
            $bin = '';
            for ($i = 0; $i < $len; $i++) {
                $bin .= strlen(decbin(ord($string[$i]))) < 8 ? str_pad(decbin(ord($string[$i])), 8, 0, STR_PAD_LEFT) : decbin(ord($string[$i]));
            }

            return $bin;
        }

        return false;

    }

    /**
     * chop a string to maximum # of words
     *
     * @param        string $str       the string we're chopping
     * @param        int    $max_words number of words to return
     * @param        string $append    what to put at the end of the string
     *
     * @return        string    the chopped string
     */
    public static function wordChop($str, $max_words = 15, $append = "...")
    {
        if (Strings::isString($str)) {
            $e = explode(' ', $str);
            $w = count($e);

            if ($w > $max_words) {
                $str = '';
                for ($i = 0; $i < $max_words; $i++) {
                    $str .= ' ' . $e[$i];
                }
                $str .= $append;
            }

            return ($str);
        } else {
            return false;
        }
    }

    /**
     *
     * determines whether the first letter is uppercase or not
     *
     * @param        string $string the string to analyze
     *
     * @return        bool
     */
    public static function isFirstLetterUpper($string)
    {
        if (Strings::isString($string)) {
            if (strstr($string[0], ucfirst($string[0]))) {
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
     * determines if one string is contained in another
     *
     * @param    string $str
     * @param    string $content
     * @param    bool   $ignorecase
     *
     * @return    bool
     */
    public static function contains($str, $content, $ignorecase = true)
    {
        if (Strings::isString($str)) {
            if ($ignorecase) {
                $str = strtolower($str);
                $content = strtolower($content);
                if (false === stripos($content, $str)) {
                    return false;
                }
            }
            if (false === strpos($content, $str)) {
                return false;
            }

            return true;

        } else {
            return false;
        }
    }

    /**
     * determines if any item in an array is found within a string
     *
     * @param    array  $array
     * @param    string $string
     *
     * @return    bool
     */
    public static function arrayItemInString($array, $string)
    {
        if (Strings::isString($string)) {
            foreach ($array AS $item) {
                if (!stristr($string, $item) === false) {
                    return true;
                }
            }

            return false;
        } else {
            return false;
        }
    }

    /**
     * takes all instances of multiple spaces and turns them into a single space
     *
     * @param    string $string the string being cleaned
     *
     * @return    string    the cleaned string
     */
    public static function collapseWhiteSpace($string)
    {
        if (Strings::isString($string)) {
            return preg_replace('/\s\s+/', ' ', $string);
        } else {
            return false;
        }
    }

    /**
     * smart capitalization of words
     *
     * @param    string $string the string to be capitalized
     *
     * @return    string    the cleaned string
     */
    public static function titleCase($string)
    {
        if (Strings::isString($string)) {
            // Our array of 'small words' which shouldn't be capitalised if
            // they aren't the first word.  Add your own words to taste.
            $smallwordsarray = array(
                'of', 'a', 'the', 'and', 'an', 'or', 'nor', 'but', 'is', 'if', 'then', 'else', 'when',
                'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to', 'into', 'with'
            );

            // Split the string into separate words
            $words = explode(' ', $string);

            foreach ($words as $key => $word) {
                // If this word is the first, or it's not one of our small words, capitalise it
                // with ucwords().
                if ($key == 0 || !in_array($word, $smallwordsarray)) {
                    $words[$key] = ucwords(strtolower($word));
                }
            }
            // Join the words back into a string
            $newtitle = implode(' ', $words);

            return $newtitle;
        }

        return false;
    }

    /**
     * eliminates all white space from a string
     *
     * @param    string $string the string being cleaned
     *
     * @return    string    the cleaned string
     */
    public static function stripWhiteSpace($string)
    {
        if (Strings::isString($string)) {
            return preg_replace('/\s+/', '', $string);
        } else {
            return false;
        }
    }

    /**
     *
     * removes specified words from a string
     *
     * @param    string $string    the string to strip words from
     * @param    array  $stopwords the array of words to strip from string
     * @param    string $return    the return type
     *                             basically if it is set to "string" it will return
     *                             a string. otherwise it will return an array
     *
     * @return    mixed - based on the value of $return
     */
    public static function stripStopWords($string, $stopwords, $return = "string")
    {
        if (Strings::isString($string)) {
            $stopwords = array_flip($stopwords);

            $strarray = explode(' ', $string); // Create an array of words from the string
            $new_text = array(); // Create an array to hold the new string

            foreach ($strarray as $key => $word) {
                if (!array_key_exists($word, $stopwords)) {
                    $new_text[] = $word; // If this word isn't a stopword, add it
                }
            }

            if ($return == "string") {
                return implode(' ', $new_text);
            } else {
                return $new_text;
            }
        } else {
            return false;
        }
    }

    /**
     * strips non alphabetical characters from a string, preserving spaces
     *
     * @param        string $string string to be cleaned
     *
     * @return        string
     */
    public static function stripNonAlpha($string)
    {
        if (Strings::isString($string)) {
            return preg_replace("/[^A-Za-z\s\s+]/", "", $string);
        } else {
            return false;
        }
    }

    /**
     * strips non alphanumeric characters from a string
     *
     * @param        string $string string to be cleaned
     * @param   bool        $preserveSpaces
     *
     * @return        string
     */
    public static function stripNonAlphanumeric($string, $preserveSpaces = true)
    {
        if (Strings::isString($string)) {
            if ($preserveSpaces == false) {
                return preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);
            } else {
                return preg_replace("/[^A-Za-z0-9\s\s+]/", "", $string);
            }
        } else {
            return false;
        }
    }

    /**
     *
     * converts a string to a specified case, such as all upper case
     *
     * @param    string $string string to be cleaned
     * @param   string  $mode   the mode to use when converting the string
     *
     * @return    string
     */
    public static function convertStringCase($string, $mode)
    {
        if (Strings::isString($string)) {
            switch ($mode) {
                case "upper":
                    return strtoupper($string);
                case "lower":
                    return strtolower($string);
                case "ucfirst":
                    return ucfirst($string);
                case "lcfirst":
                    return lcfirst($string);
                case "ucwords":
                    return ucwords($string);
                default:
                    return $string;
            }
        } else {
            return false;
        }
    }

    /**
     *
     * get the length of a string
     *
     * @param    string $string string to be checked
     * @param   bool    $noHTML whether or not HTML should be stripped first
     *
     * @return    string
     */
    public static function getStringLength($string, $noHTML = true)
    {
        if (Strings::isString($string)) {
            if ($noHTML == true) {
                strip_tags($string);
            }

            return Strings::processFunction($string, 'strlen');
        } else {
            return false;
        }
    }

    /**
     *
     * @param   string $string
     * @param   int    $limit
     *
     * @return  string
     */
    public static function summarize($string, $limit)
    {
        if (Strings::isString($string)) {
            $tok = strtok($string, " ");
            $text = "";
            $words = '0';
            while ($tok) {
                $text .= " " . $tok;
                $words++;
                if (($words >= $limit) && ((substr($tok, -1) == "!") || (substr($tok, -1) == ".")))
                    break;
                $tok = strtok(" ");
            }

            return ltrim($text);
        } else {
            return false;
        }
    }

    /**
     *
     * @param   string $string
     *
     * @return  string
     */
    public static function stringReverse($string)
    {
        if (Strings::isString($string)) {
            return Strings::processFunction($string, 'strrev');
        } else {
            return false;
        }
    }

    /**
     * @param $string
     *
     * @return bool
     */
    public static function containsHTML($string)
    {
        if (false == preg_match('#(?<=<)\w+(?=[^<]*?>)#', $string)) {
            return false;
        }

        return true;
    }

    /**
     *
     * determines if a string contains newline characters
     *
     * @param    string $string the string to test
     *
     * @return    bool    true, if newlines are found, false otherwise
     */
    public static function containsNewLines($string)
    {
        if (Strings::isString($string)) {
            if (preg_match("/(%0A|%0D|\\n+|\\r+)/i", $string) != 0) {
                return true;
            } else {
                return false;
            }

        }

        return false;
    }

    /**
     *
     * this function formats text
     *
     * @param    string $string       the string of text to be converted
     * @param    string $mode         the type of formatting to be done
     * @param    string $length       the length, in # of chars, the string should be
     * @param    bool   $use_title    passed to str_truncate, whether to use the title
     *                                attribute to display the full text
     * @param   string  $allowed_tags tags allowed to be included
     *
     * @return    string    the same input string is redisplayed in a new format
     */
    public static function processText($string, $mode = "plain", $length = "", $use_title = false, $allowed_tags = null)
    {

        // first things first, if the string of text is
        // less than 1 characters (IOW it is blank), then
        // we just dump it and replace it with "--"
        if (strlen($string) < 1) {
            $string = "--";
        } // otherwise, continue
        else {
            // process as plain text, with links and line breaks added
            if ($mode == "plain") {
                $string = Strings::plainTextDisplay($string);
            } // process as true plain text
            elseif ($mode == "stripped") {
                $string = strip_tags($string);
                $string = htmlspecialchars($string);
            } elseif ($mode == "html") {
                $string = strip_tags($string, $allowed_tags);
            } // process as plain text, shortened to $length
            elseif ($mode == "truncated") {
                $length = (int)$length;

                // Safety check: if the length param was set, we continue
                if ($length > 0) {
                    $string = strip_tags($string);
                    //$string = htmlspecialchars($string);
                    $string = Strings::truncateString($string, $length, $use_title);
                } // if the length parameter is not greater than zero,
                // we have not passed the safety check and we should just
                // treat it as "stripped"
                else {
                    $string = strip_tags($string);
                    $string = htmlspecialchars($string);
                }
            } // if the mode hasn't been set, leave it alone
            else {
                return $string;
            }
        }

        return $string;
    }

    /**
     * @param $sString
     * @param $aWords
     * @param $attrs
     *
     * @return bool|mixed
     */
    public static function highlight($sString, $aWords, $attrs)
    {
        if (!is_array($aWords) || empty($aWords) || !is_string($sString)) {
            return false;
        }

        $sWords = implode('|', $aWords);

        return preg_replace('@\b(' . $sWords . ')\b@si', "<span $attrs>$1</span>", $sString);
    }

    /**
     *
     * pulls e-mail addresses from a string
     *
     * @param    string $string the string we're looking in
     *
     * @return    array    an array of e-mail addresses
     */
    public static function extract_emails($string)
    {
        preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);

        return $matches[0];
    }

    /**
     *
     * takes a string of numbers and, for those numbers <10, adds '0' to them.
     * i.e. '1' becomes '01'.  Useful when making dates
     *
     * @param int $num
     *
     * @return string
     */
    public static function prependNum($num)
    {
        if ($num < 10) {
            return "0" . $num;
        }

        return $num;
    }

    /**
     *
     * spell checks a single word
     * requires pspell be bundled and configured (which is not the case as of PHP 5.3.0)
     *
     * @param string $word
     * @param string $lang
     *
     * @return bool
     */
    public static function spellCheckWord($word, $lang = 'en')
    {
        $pspell_link = pspell_new($lang);

        if (pspell_check($pspell_link, $word)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * determines whether to display a string of text based on some condition
     * may also prepend or append additional text based on the determination that
     * the condition was satisfied.
     *
     * @param        $string
     * @param string $condition
     * @param string $prepend
     * @param string $append
     * @param bool   $echo
     *
     * @return string
     */
    public static function conditional_display($string, $condition = "strlen", $prepend = "", $append = "", $echo = false)
    {
        $output = '';

        // POSSIBLE CONDITIONS
        // strlen - string length greater than '2'
        // bool - boolean
        // isset - variable is set
        // alnum - alphanumeric
        // alpha - alphabetical only
        // num - numeric only
        // print - printable chars

        switch ($condition) {
            // String Length > 2
            case "strlen":
                if (strlen($string) < 2) {
                    $condition = false;
                }
                break;

            // boolean
            case "bool":
                if (false === $string) {
                    $condition = false;
                } else {
                    $condition = true;
                }
                break;

            // Variable isset
            case "isset":
                if (isset($string)) {
                    $condition = true;
                }
                break;

            // Alphanumeric only
            case "alnum":
                if (ctype_alnum($string)) {
                    $condition = true;
                }
                break;

            // Alphabetical only
            case "alpha":
                if (ctype_alpha($string)) {
                    $condition = true;
                }
                break;

            // Numeric only
            case "num":
                if (ctype_digit($string)) {
                    $condition = true;
                }
                break;

            // Printable characters only
            case "print":
                if (ctype_print($string)) {
                    $condition = true;
                }
                break;
            default:
                $condition = true;
        }

        if ($condition == true) {
            $output = $prepend . $string . $append;
        } else {
            $output = '';
        }

        if ($echo == true) {
            echo $output = '';
        } else {
            return $output;
        }
    }

    /**
     * determines whether a string contains only numbers
     *
     * @param    int $input_number the number being checked
     *
     * @return    bool    TRUE if only a number, else FALSE
     */
    public static function num_only($input_number)
    {

        if (!preg_match("/^([0-9]+)$/", $input_number)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * cleans up strings so they can be used in URLS
     *
     * @author "Borek" - attributed to a post located at:
     *         http://drupal.org/node/63924
     *
     * @param   string $string the string we're cleaning
     *
     * @return  string  the input string, ready to go
     */
    public static function pathauto_cleanstring($string)
    {
        $url = $string;
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url); // substitutes anything but letters, numbers and '_' with separator
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url); // TRANSLIT does the whole job
        $url = strtolower($url);
        $url = preg_replace('~[^-a-z0-9_]+~', '', $url); // keep only letters, numbers, '_' and separator
        return $url;
    }

    /**
     *
     * determines if there's any bad stuff in a string
     *
     * @param   string $string the string to test
     *
     * @return  bool    true, if bad stuff has been found, false otherwise
     */
    public static function contains_bad_str($string)
    {
        if (Strings::isString($string)) {
            $bad_strings = array(
                "content-type:",
                "mime-version:",
                "multipart/mixed",
                "Content-Transfer-Encoding:",
                "bcc:",
                "cc:",
                "to:");


            foreach ($bad_strings as $bad_string) {
                if (eregi($bad_string, strtolower($string))) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * This function takes a numeric value, and adds commas where applicable.
     * If you gave it the value of "2930829523", then it would return "2,930,829,523"
     *
     * @author    tyutyu1@vodafone.hu
     *
     * @param    int $value
     *
     * @return    string
     */
    public static function num_comma($value)
    {
        if (strpos($value, ".")) {
            $decimalval = substr($value, strpos($value, ".") + 1);
            $value = substr($value, 0, strpos($value, "."));
        }

        $length = strlen($value);

        for ($i = 3; $i < ($length); $i = $i + 3) {
            $k = $i * (-1);
            $chunks[count($chunks)] = substr($value, $k, 3);
        }

        $inarray = count($chunks) * 3;
        $leftout = $length - $inarray;
        $leftout = substr($value, 0, $leftout);

        $finaltext = $leftout;

        rsort($chunks);

        for ($i = 0; $i < count($chunks); $i++) {
            $finaltext .= "," . $chunks[$i];
        }

        if (strlen($decimalval) > 0) {
            $finaltext .= "." . $decimalval;
        }

        return $finaltext;
    }

    /**
     * @param      $string
     * @param bool $removeEmpty
     *
     * @return array|bool|string
     */
    public static function linesToArray($string, $removeEmpty = true)
    {
        if (Strings::isString($string)) {
            $array = preg_split('/$\R?^/m', $string);
            if ($removeEmpty === true) {
                $array = Arrays::arrayRemoveEmpty($array);
            }

            return $array;
        } else {
            return false;
        }
    }

    /**
     * simple wrapper function to validate a string using the ctype_* functions
     *
     * @param    string $string the string to be validated
     * @param    string $mode   the validation mode, must adhere to one of the types below
     *
     * @return    bool
     */
    public static function ctype_validate($string, $mode)
    {
        switch ($mode) {
            case "alnum":
                // check to see if $string ONLY contains alphanumeric characters
                if (ctype_alnum($string)) {
                    return true;
                }
                break;

            case "alpha":
                // check to see if $string ONLY contains alphabetical characters
                if (ctype_alpha($string)) {
                    return true;
                }
                break;

            case "upper":
                // check to see if $string ONLY contains uppercase letters
                if (ctype_upper($string)) {
                    return true;
                }
                break;

            case "lower":
                // check to see if $string ONLY contains lowercase letters
                if (ctype_lower($string)) {
                    return true;
                }
                break;

            case "digit":
                // check to see if $string ONLY contains numeric characters
                if (ctype_digit($string)) {
                    return true;
                }
                break;

            case "xdigit":
                // check to see if $string is ALL hexadecimal digits
                if (ctype_xdigit($string)) {
                    return true;
                }
                break;

            case "space":
                // check to see if $string ONLY contains whitespace characters (such as \n, \r, \t)
                if (ctype_space($string)) {
                    return true;
                }
                break;

            case "punct":
                // check to see if $string ONLY contains punctuation characters
                if (ctype_punct($string)) {
                    return true;
                }
                break;

            case "print":
                // check to see if $string ONLY contains printable 
                // characters.
                // Or, to be more clear, checks that it only contains 
                // alphabetical, numeric, or punctuation characters
                if (ctype_print($string)) {
                    return true;
                }
                break;

            case "cntrl":
                // check to see if $string ONLY contains control 
                // characters
                if (ctype_cntrl($string)) {
                    return true;
                }
                break;
            default:
                return false;
        }
    }

    /**
     * changes an integer to a roman numeral
     *
     * @param    int $integer the integer to convert
     *
     * @return    string
     */
    public static function integerToRoman($integer)
    {
        // Convert the integer into an integer (just to make sure)
        $integer = intval($integer);
        $result = '';

        // Create a lookup array that contains all of the Roman numerals.
        $lookup = array('M'  => 1000, 'CM' => 900, 'D' => 500, 'CD' =>
            400, 'C'         => 100,
                        'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10,
                        'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);

        foreach ($lookup as $roman => $value) {
            // Determine the number of matches
            $matches = intval($integer / $value);

            // Add the same number of characters to the string
            $result .= str_repeat($roman, $matches);

            // Set the integer to be the remainder of the integer and the value
            $integer = $integer % $value;
        }

        // The Roman numeral should be built, return it
        return $result;
    }

    /**
     *
     * @param   string $string
     *
     * @return  string
     */
    public static function utf8_htmlspecialchars($string)
    {
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }

    /**
     *
     * @param   string $string
     *
     * @return  int
     */
    public static function utf8_strlen($string)
    {
        return strlen(utf8_decode($string));
    }


    /**
     * @param $element
     *
     * @return int
     */
    public static function isLetters($element)
    {
        return preg_match("/[^A-z]/", $element);
    }

    /**
     * @param $string
     *
     * @return array
     */
    public static function makeWordsArray($string)
    {
        $string = preg_split("/\s+/", $string, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($string AS $word) {
            if (Strings::isLetters($word)) {
                $output[] = strtolower($word);
            }
        }

        return $output;
    }

    /**
     * Count the number of bytes of a given string.
     * Input string is expected to be ASCII or UTF-8 encoded.
     * Warning: the function doesn't return the number of chars
     * in the string, but the number of bytes.
     *
     * @param string $str The string to compute number of bytes
     *
     * @return int The length in bytes of the given string.
     */
    public static function strBytes($str)
    {
        // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT

        // Number of characters in string
        $strlen_var = strlen($str);

        // string bytes counter
        $d = 0;

        /*
         * Iterate over every character in the string,
         * escaping with a slash or encoding to UTF-8 where necessary
         */
        for ($c = 0; $c < $strlen_var; ++$c) {

            $ord_var_c = ord($str{$d});

            switch (true) {
                case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                    // characters U-00000000 - U-0000007F (same as ASCII)
                    $d++;
                    break;

                case (($ord_var_c & 0xE0) == 0xC0):
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $d += 2;
                    break;

                case (($ord_var_c & 0xF0) == 0xE0):
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $d += 3;
                    break;

                case (($ord_var_c & 0xF8) == 0xF0):
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $d += 4;
                    break;

                case (($ord_var_c & 0xFC) == 0xF8):
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $d += 5;
                    break;

                case (($ord_var_c & 0xFE) == 0xFC):
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $d += 6;
                    break;
                default:
                    $d++;
            }
        }

        return $d;
    }

    /**
     * Replaces a blank string with a different string. Used to make stuff more user-friendly,
     * rather than just showing nothing (which is how a blank string looks) we'd show something that represents blankness
     *
     * @param        $str
     * @param int    $len
     * @param string $sub
     *
     * @return string
     */
    public static function subWhenBlank($str, $len = 1, $sub = '--')
    {
        if (strlen(trim($str)) < $len) {
            return $sub;
        }

        return $str;
    }

    /**
     * Creates a V1 compatible UUID
     */
    public static function uniqueID()
    {
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid =
            substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);

        return $uuid;
    }

    /**
     * this version of PHP's built-in str_pad is modified to add white space around words
     * By default it is set to add 1 space in front of and behind the string, though this
     * method can be used just like the native str_pad, with one exception: the 2nd parameter, $num,
     * is meant to serve as how many *extra* chars are to be added, whereas the native str_pad's 2nd param
     * is how long the final string is
     *
     * @param        $string
     * @param int    $num
     * @param string $padString
     * @param int    $type
     *
     * @return string
     */
    public static function strPad($string, $num = 2, $padString = ' ', $type = STR_PAD_BOTH)
    {
        if (Strings::isString($string)) {
            return str_pad($string, strlen($string) + $num, $padString, $type);
        }

        return false;
    }


    /**
     * @param object $obj   the decoded JSON string
     * @param string $field the field to search in
     * @param string $value the value to search for
     *
     * @return string
     */
    public static function searchJson($obj, $field, $value)
    {
        foreach ($obj as $item) {
            foreach ($item as $child) {
                if (isset($child->$field) && $child->$field == $value) {
                    return $child;
                }
            }
        }

        return null;
    }


}
