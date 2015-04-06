<?php

/**
 *
 *  Class for working with arrays
 *
 */
class Arrays
{

    /**
     *
     * Validates whether the given element is an array.
     *
     * @return bool
     *
     * @param  array $element
     * @param  bool  $require_content If the array can be empty or not
     */
    public static function isArray($element, $require_content = true)
    {
        return (!is_array($element)) ? false : ($require_content && empty($element) ? false : true);
    }

    /**
     *
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
     *
     * searches for an item in an array, without being case sensitive
     *
     * @param    string $search The item we're checking for
     * @param    array  &$array The array we're looking in
     *
     * @return    bool
     */
    public static function inArrayNoCase($search, &$array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        $search = strtolower($search);
        foreach ($array as $item) {
            if (strtolower($item) == $search) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * mostly for debug purposes. this function loops through
     * an array and prints it
     *
     * @param array  $array
     * @param string $tag
     * @param bool   $printoutput
     *
     * @return string
     */
    public static function printArray($array, $tag = "p", $printoutput = true)
    {
        $output = '';

        if (!Arrays::isArray($array, false)) {
            $array = (array)$array;
        }

        foreach ($array AS $key => $val) {
            if (is_null($tag)) {
                $output .= " $key: $val \n";
            } else {
                $output .= " <$tag>$key: $val</$tag>\n ";
            }
        }

        if ($printoutput == true) {
            echo $output = '';
        } else {
            return $output;
        }
    }

    /**
     *
     * take an item out of an array
     *
     * @param   array   $haystack the array in which we're looking
     * @param    string $needle   the item we're removing
     *
     * @return    array
     */
    public static function removeArrayItem($haystack, $needle)
    {
        if (!Arrays::isArray($haystack, false)) {
            return false;
        }

        $tmp = $haystack;
        unset($tmp[array_search($needle, $tmp)]);

        return $tmp;
    }

    /**
     *
     * take an item out of an array by naming its key
     *
     * @param    array  $array the array in which we're looking
     * @param    string $item  the item we're removing
     *
     * @return    array
     */
    public static function removeArrayItemByKey($array, $item)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $cleaned_array = array();

        foreach ($array AS $key => $val) {
            if ($key !== $item) {
                $cleaned_array[$key] = $val;
            }
        }

        return $cleaned_array;
    }

    /**
     *
     * count the number of times an item from $array appear in $string
     *
     * @param    array  $array  the array we're using to compare
     * @param    string $string the string we're looking in
     *
     * @return    array
     */
    public static function arrayCompare($array, $string)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $matches = 0;
        foreach ($array AS $item) {
            if (stristr($string, $item) == true) {
                $matches++;
            }
        }

        return $matches;
    }

    /**
     * gets rid of any duplicate entries in an array
     *
     * @param    array $array the array to be cleaned
     *
     * @return    array    the cleaned array
     */
    public static function cleanArrayDupes($array)
    {
        if (Arrays::isArray($array, false)) {
            return false;
        }

        $n = 0;
        $items = array_values(array_unique($array));
        $cc = count($items);
        for ($i = 0; $i < $cc; $i++) {
            if ($items[$i] != '') {
                $output[$n] = $items[$i];
                $n++;
            }
        }

        return $output;
    }

    /**
     *
     * works in conjunction with the function below to sort arrays
     * by the string length of the individual pieces
     *
     * @param   string $val_1
     * @param   string $val_2
     *
     * @return  bool
     */
    public static function asciiSort($val_1, $val_2)
    {
        // initialize the return value to zero
        $retVal = 0;

        // compare lengths
        $firstVal = strlen($val_1);
        $secondVal = strlen($val_2);

        if ($firstVal > $secondVal) {
            $retVal = 1;
        } else if ($firstVal < $secondVal) {
            $retVal = -1;
        }

        return $retVal;
    }

    /**
     *
     * sorts an array in order of string length, with longest strings first.
     *
     * @param    array $array the array being sorted
     *
     * @return    array    a new array is created
     */
    public static function sortArrayOnLength($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        uasort($array, "asciiSort");
        foreach ($array as $val) {
            $output[] = $val;
        }

        $output = array_reverse($output);

        return $output;
    }

    /**
     *
     * compares two arrays to check if they're identical
     *
     * @param    array $op1 the first array
     * @param    array $op2 the second array
     *
     * @return    bool
     */
    public static function arrayIdentical($op1, $op2)
    {
        if ((!Arrays::isArray($op1, false)) || (!Arrays::isArray($op2, false))) {
            return false;
        }

        if (count($op1) < count($op2)) {
            return false;
            // $op1 < $op2
        } elseif (count($op1) > count($op2)) {
            return false;
            // $op1 > $op2
        }

        foreach ($op1 as $key => $val) {
            if (!array_key_exists($key, $op2)) {
                return false;
                // uncomparable
            } elseif ($val < $op2[$key]) {
                return false;
            } elseif ($val > $op2[$key]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Scrubs blank, null, and false values from an array
     *
     * @param $array
     *
     * @return array|bool
     */
    public static function arrayScrub($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $narr = array();

        while (list($key, $val) = each($array)) {
            if (is_array($val)) {
                $val = Arrays::arrayScrub($val);

                // does the result array contain anything?
                if (count($val) != 0) {
                    // yes :-)
                    $narr[$key] = $val;
                }
            } else {
                if ((false !== $val) && (!is_null($val))) {
                    if (trim($val) != '') {
                        $narr[$key] = $val;
                    }
                }
            }
        }
        unset($array);

        return $narr;
    }

    /**
     * @param $array
     *
     * @return bool|mixed
     */
    public static function arrayMin($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $array = Arrays::arrayScrub($array);

        return min($array);

    }

    /**
     * @param $array
     *
     * @return bool|mixed
     */
    public static function arrayMax($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $array = Arrays::arrayScrub($array);

        return max($array);
    }

    /**
     * @param $array
     *
     * @return bool|float
     */
    public static function arrayAvg($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $array = Arrays::arrayScrub($array);

        $totNums = count($array);

        $sum = 0;

        foreach ($array AS $val) {
            $sum = $sum + $val;
        }

        return $sum / $totNums;
    }

    /**
     *
     * removes empty items from an array
     *
     * @param   array $array
     *
     * @return  string
     */
    public static function arrayRemoveEmpty($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $narr = array();

        while (list($key, $val) = each($array)) {
            if (is_array($val)) {
                $val = Arrays::arrayRemoveEmpty($val);
                // does the result array contain anything?
                if (count($val) != 0) {
                    // yes :-)
                    $narr[$key] = $val;
                }
            } else {
                if (trim($val) != "") {
                    $narr[$key] = $val;
                }
            }
        }
        unset($array);

        return $narr;
    }

    /**
     *
     * @param   array $array
     * @param   int   $position
     *
     * @return  string
     */
    public static function arrayKeyName($array, $position)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        $temp = array_slice($array, $position, 1, true);

        return key($temp);
    }

    /**
     * @param        $array
     * @param string $mode
     * @param int    $flags
     * @param null   $function
     *
     * @return bool
     */
    public static function arraySort(&$array, $mode = "normal", $flags = SORT_REGULAR, $function = null)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        switch ($mode) {
            case "key_reverse" :
                return krsort($array, $flags);
            // sorts by key, preserves keys
            case "assoc" :
                return asort($array, $flags);
            // sorts by value, preserves keys
            case "assoc_reverse" :
                return arsort($array, $flags);
            // sorts by value, preserves keys
            case "keys" :
                return ksort($array, $flags);
            // sorts by key, preserves keys
            case "natcase" :
                return natcasesort($array);
            // sorts by value, preserves keys, 	natural, case insensitive
            case "natural" :
                return natsort($array);
            // sorts by value, preserves keys
            case "reverse" :
                return rsort($array, $flags);
            // sorts by value, does NOT preserve keys
            case "random" :
                return shuffle($array);
            // sorts by value, does NOT preserve keys
            case "normal" :
                return sort($array, $flags);
            //	sorts by value, does NOT preserve keys
            case "uasort" :
                return uasort($array, $function);
            // sorts by value, preserves keys
            case "uksort" :
                return uksort($array, $function);
            // sorts by value, preserves keys
            case "usort" :
                return usort($array, $function);
            //	sorts by value, does NOT preserve keys
            case "shuffle_assoc" :
                return Arrays::shuffleAssoc($array);
            default :
                return sort($array);
        }
    }

    /**
     *
     * @param   array $array
     *
     * @return  bool
     */
    public static function shuffleAssoc(&$array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        if (count($array) > 1) { //$keys needs to be an array, no need to shuffle 1 item anyway
            $keys = array_rand($array, count($array));
            foreach ($keys as $key) {
                $new[$key] = $array[$key];
            }
            $array = $new;
        }

        return true;
        //because it's a wannabe shuffle(), which returns true
    }

    /**
     *
     * @param   array $keys
     * @param   array $values
     *
     * @return  array
     */
    public static function arrayCombine($keys, $values)
    {
        if ((!Arrays::isArray($keys, false)) || (!Arrays::isArray($values, false))) {
            return false;
        }

        return array_combine($keys, $values);
    }

    /**
     *
     * @param   array $array
     * @param   int   $size
     * @param   bool  $preserve_keys
     *
     * @return  array
     */
    public static function arrayChunk($array, $size, $preserve_keys = false)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        return array_chunk($array, $size, $preserve_keys);
    }

    /**
     * @param     $array
     * @param int $case
     *
     * @return array|bool
     */
    public static function arrayChangeKeyCase($array, $case = CASE_LOWER)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        return array_change_key_case($array, $case);
    }

    /**
     *
     * @param   mixed $start
     * @param   mixed $end
     * @param int     $step
     *
     * @return  array
     */
    public static function arrayRange($start, $end, $step = 1)
    {
        return range($start, $end, $step);
    }

    /**
     *
     * @param   array $array
     *
     * @return  bool
     */
    public static function arrayIsAssoc($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        if (array_keys($array) === range(0, sizeof($array) - 1)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * counts elements of an multidimensional array
     *
     * @param   array $array Input Array
     * @param   int   $limit dimensions that shall be considered (-1 means no limit )
     *
     * @return  int     counted elements
     */
    public static function arrayMultiCount($array, $limit = -1)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        $cnt = 0;
        $limit = $limit > 0 ? (int)$limit : -1;
        $arrs[] = $array;
        for ($i = 0; isset($arrs[$i]) && is_array($arrs[$i]); ++$i) {
            foreach ($arrs[$i] as $value) {
                if (!is_array($value))
                    ++$cnt;
                elseif ($limit == -1 || $limit > 1) {
                    if ($limit > 1)
                        --$limit;
                    $arrs[] = $value;
                }
            }
        }

        return $cnt;
    }

    /**
     *
     * @param   array $array
     *
     * @return  array
     */
    public static function arrayCount($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        return Arrays::processFunction($array, 'count');
    }

    /**
     *
     * @param   string $item
     * @param   array  $array
     * @param   bool   $strict
     *
     * @return  bool
     */
    public static function inArray($item, $array, $strict = false)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }

        return in_array($item, $array, $strict);
    }

    /**
     *
     * @return  array
     */
    public static function array_merge_unique()
    {
        $args = func_get_args();
        $total = count($args);

        if ($total < 2) {
            return false;
        }

        $i = 0;
        $j = 0;
        $arr = array();

        foreach ($args as $arg) {
            foreach ($arg as $v) {
                $arr[$j] = $v;
                $j += $total;
            }

            $i++;
            $j = $i;
        }

        ksort($arr);

        return array_unique($arr);
    }

    /**
     *
     * determines if the array is multidimensional or not
     *
     * @param   array $array the array we're working with
     *
     * @return  bool
     */
    public static function isMultidimensionalArray($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        // loop through the array
        // if any of the values are themselves an array
        // then this is multidimensional.  jump out and return true
        foreach ($array AS $val) {
            if (is_array($val)) {
                return true;
            }
        }

        // otherwise, this is not a multidimensional array
        return false;
    }

    /**
     *
     * @param int $start
     * @param int $end
     *
     * @return array
     */
    public static function prependArrayNums($start, $end)
    {
        for ($i = $start; $i <= $end; $i++) {
            $x[] = Strings::prependNum($i);
        }

        return $x;
    }

    /**
     *
     * Calculates Mean, Median, Mode, or Range from an array
     *
     * @param   array  $array  the array we're working with
     * @param   string $output the type of information we want (i.e. the 'mean')
     *
     * @return  int
     */
    public static function mmmr($array, $output = 'mean')
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        switch ($output) {
            // mean = the average of all items in the array
            case 'mean' :
                $count = count($array);
                $sum = array_sum($array);
                $total = $sum / $count;
                break;
            // median = the middle value after all the numbers are sorted smallest to largest
            case 'median' :
                rsort($array);
                $middle = round(count($array) / 2);
                $total = $array[$middle - 1];
                break;
            // mode = the number that is in the array the most times
            case 'mode' :
                $v = array_count_values($array);
                arsort($v);
                foreach ($v as $k => $val) {
                    $total = $k;
                    break;
                }
                break;
            // range = the difference between the highest number and the lowest number
            case 'range' :
                sort($array);
                $sml = $array[0];
                rsort($array);
                $lrg = $array[0];
                $total = $lrg - $sml;
                break;
        }

        return $total;
    }

    /**
     *
     * get the printable version of a piped option
     *
     * @param   string $string the piped option to convert to printable item
     * @param   array  $array  the source array of piped options
     *
     * @return  string
     */
    public static function get_piped_opt_printable($string, $array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        foreach ($array AS $val) {
            list($item, $code) = explode("|", $val);
            if ($string == $code) {
                return $string;
            }
        }
    }

    /**
     * determines if an array item is found within a string
     *
     * @param    array  $array
     * @param    string $string
     *
     * @return    bool
     */
    public static function arrayItemInString($array, $string)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        foreach ($array AS $item) {
            if (!stristr($string, $item) === false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public static function shuffleAndPick($array)
    {
        if (!Arrays::isArray($array, false)) {
            return false;
        }
        shuffle($array);

        return $array[0];
    }

    /**
     *
     * @param   array $arr
     *
     * @return  string
     */
    public static function arrayToJSON($arr)
    {
        if (!Arrays::isArray($arr, false)) {
            return false;
        }
        if (!is_array($arr)) {
            return false;
        }

        if (function_exists('json_encode')) {
            return json_encode($arr);
        }
        //Lastest versions of PHP already has this functionality.
        $parts = array();
        $is_list = false;

        //Find out if the given array is a numerical array
        $keys = array_keys($arr);
        $max_length = count($arr) - 1;
        if (($keys[0] == 0) and ($keys[$max_length] == $max_length)) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for ($i = 0; $i < count($keys); $i++) { //See if each key corresponds to its position
                if ($i != $keys[$i]) { //A key fails at position check.
                    $is_list = false;
                    //It is an associative array.
                    break;
                }
            }
        }

        foreach ($arr as $key => $value) {
            if (is_array($value)) { //Custom handling for arrays
                if ($is_list) {
                    $parts[] = Arrays::arrayToJSON($value);
                    /* :RECURSION: */
                } else {
                    $parts[] = '"' . $key . '":' . Arrays::arrayToJSON($value);
                    /* :RECURSION: */
                }
            } else {
                $str = '';
                if (!$is_list) {
                    $str = '"' . $key . '":';
                }
                //Custom handling for multiple data types
                if (is_numeric($value)) {
                    $str .= $value;
                    //Numbers
                } elseif ($value === false) {
                    $str .= 'false';
                    //The booleans
                } elseif ($value === true) {
                    $str .= 'true';
                } else {
                    $str .= '"' . addslashes($value) . '"';
                    //All other things
                }
                // @Note Is there any more datatype we should be in the lookout for? (Object?)

                $parts[] = $str;
            }
        }
        $json = implode(',', $parts);

        if ($is_list) {
            return '[' . $json . ']';
            //Return numerical JSON
        }

        return '{' . $json . '}';
        //Return associative JSON
    }

    /**
     * @param     $theArray
     * @param int $tabCount
     *
     * @return bool|string
     */
    public static function arrayToXML($theArray, $tabCount = 2)
    {
        if (!Arrays::isArray($theArray, false)) {
            return false;
        }
        $theXML = '';
        $tabCount++;
        $tabSpace = "";
        $extraTabSpace = "";
        for ($i = 0; $i < $tabCount; $i++) {
            $tabSpace .= "\t";
        }

        for ($i = 0; $i < $tabCount + 1; $i++) {
            $extraTabSpace .= "\t";
        }

        // parse the array for data and output xml
        foreach ($theArray as $tag => $val) {
            if (!is_array($val)) {
                $theXML .= PHP_EOL . $tabSpace . '<' . $tag . '>' . htmlentities($val) . '</' . $tag . '>';
            } else {
                $tabCount++;
                $theXML .= PHP_EOL . $extraTabSpace . '<' . $tag . '>' . Arrays::arrayToXML($val, $tabCount);
                $theXML .= PHP_EOL . $extraTabSpace . '</' . $tag . '>';
            }
        }

        return $theXML;
    }

    /**
     * Returns an array of multiple random numbers
     *
     * @param      $num
     * @param int  $min
     * @param null $max
     *
     * @return array
     */
    public static function multipleRandNums($num, $min = 0, $max = null)
    {
        if (is_null($max)) {
            $max = mt_getrandmax();
        }

        for ($x = 0; $x < $num; $x++) {
            $output[] = mt_rand($min, $max);
        }

        return $output;
    }
}
