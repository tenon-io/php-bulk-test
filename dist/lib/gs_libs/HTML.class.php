<?php

/**
 *
 * Class for working with/ manipulating HTML content. This class is separate
 * from the strings class because of the distinction that the strings class
 * has more to do the the text nodes of HTML whereas this class mostly operates
 * with the element nodes.  This is sometimes a difficult distinction to make
 * and there may at times be some overlap
 */
class HTML
{

    /**
     *
     * trims the text from a link so it doesn't b0rk anything
     *
     * @param    string $link  the link
     * @param    int    $limit the number of characters allowed
     *
     * @return    string    the same input string is returned, shortened
     */
    public static function trim_link($link, $limit = 50)
    {
        if ($limit > 39) {
            //  we test the limit that it's at least 39 characters,
            //  otherwise our presentation will look awkward!
            //  figure out the total length of the link title
            if (strlen($link) > $limit) {
                // edit the link
                // we also return the last 10 characters of the long
                // URL i.e. the '-10' value in the line below
                $link = substr($link, 0, ($limit / 2)) . '...' . substr($link, -10);
            }
        }

        // return the processed string
        return $link;
    }

    /**
     *
     * automatically creates links out of strings that start with 'www' or 'http'
     *
     * @author    Brian Huisman - http://www.greywyvern.com
     *
     * @param    string $string string of text
     *
     * @return    string    the same input string is redisplayed with the links added
     */
    public static function addLinks($string)
    {
        $string = preg_replace("/(?<!quot;|[=\']|:\/\/)\b((\w+:\/\/|www\.).+?)(?=\W*([<>\s]|$))/i", "<a href=\"$1\">$1</a>", $string);

        return preg_replace("/href=\"www/i", "href=\"http://www", $string);
    }

    /**
     *
     * does the same as above, except for email addresses.
     *
     * @param    string $string string of text
     *
     * @return    string    the same input string is redisplayed with the email links added
     */
    public static function email_links($string)
    {
        return preg_replace('/([a-zA-Z0-9_\-\.]*@\\S+\\.\\w+)/', '<a href="mailto:$1">$1</a>', $string);
    }

    /**
     *
     * pulls e-mail addresses from a string
     *
     * @param    string $string the string we're looking in
     *
     * @return    array    an array of e-mail addresses
     */
    public static function extractEmails($string)
    {
        preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $string, $matches);

        return $matches[0];
    }

    /**
     *
     * @param string $str
     *
     * @return array
     */
    public static function getLinks($str)
    {
        /* * * return array ** */
        $ret = array();

        /*         * * a new dom object ** */
        $dom = new domDocument;

        /*         * * get the HTML (suppress errors) ** */
        @$dom->loadHTML($str);

        /*         * * remove silly white space ** */
        $dom->preserveWhiteSpace = false;

        /*         * * get the links from the HTML ** */
        $links = $dom->getElementsByTagName('a');

        /*         * * loop over the links ** */
        foreach ($links as $tag) {
            $ret[$tag->getAttribute('href')] = $tag->childNodes->item(0)->nodeValue;
        }

        return $ret;
    }

    /**
     *
     * removes images from a string
     *
     * @param   string $string the string clean
     *
     * @return  string
     */
    public static function removeImages($string)
    {
        return preg_replace("/(<img)(.*?)(>)/si", "", $string);
    }

    /**
     *
     * gets all of the images
     *
     * @param   string $data
     */
    public static function getAllImages($data)
    {
        $images = array();
        preg_match_all('/(img|src)\=(\"|\')[^\"\'\>]+/i', $data, $media);
        unset($data);
        $data = preg_replace('/(img|src)(\"|\'|\=\"|\=\')(.*)/i', "$3", $media[0]);
        foreach ($data as $url) {
            $info = pathinfo($url);
            if (isset($info['extension'])) {
                if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png'))
                    array_push($images, $url);
            }
        }
    }

    /**
     *
     * @param   string $page
     *
     * @return  string
     */
    public static function get_title_tag($page)
    {
        $source = file_get_contents($page);
        if (false === $source) {
            return false;
        }

        return HTML::getTitleFromSource($source);
    }

    /**
     *
     * @param   string $content
     *
     * @return  string
     */
    public static function getTitleFromSource($content)
    {
        if (eregi("<title>(.*)</title>", $content, $out)) {
            return $out[1];
        } else {
            return false;
        }
    }

    /**
     *
     * @param   string $str
     * @param bool|int $count
     *
     * @return  bool
     */
    public static function containsHTML($str, $count = false)
    {
        $html = array('A', 'ABBR', 'ACRONYM', 'ADDRESS', 'APPLET', 'AREA', 'B', 'BASE', 'BASEFONT', 'BDO', 'BIG', 'BLOCKQUOTE', 'BODY', 'BR', 'BUTTON', 'CAPTION', 'CENTER', 'CITE', 'CODE', 'COL', 'COLGROUP', 'DD', 'DEL', 'DFN', 'DIR', 'DIV', 'DL', 'DT', 'EM', 'FIELDSET', 'FONT', 'FORM', 'FRAME', 'FRAMESET', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'HEAD', 'HR', 'HTML', 'I', 'IFRAME', 'IMG', 'INPUT', 'INS', 'ISINDEX', 'KBD', 'LABEL', 'LEGEND', 'LI', 'LINK', 'MAP', 'MENU', 'META', 'NOFRAMES', 'NOSCRIPT', 'OBJECT', 'OL', 'OPTGROUP', 'OPTION', 'P', 'PARAM', 'PRE', 'Q', 'S', 'SAMP', 'SCRIPT', 'SELECT', 'SMALL', 'SPAN', 'STRIKE', 'STRONG', 'STYLE', 'SUB', 'SUP', 'TABLE', 'TBODY', 'TD', 'TEXTAREA', 'TFOOT', 'TH', 'THEAD', 'TITLE', 'TR', 'TT', 'U', 'UL', 'VAR');
        if (preg_match_all("~(<\/?)\b(" . implode('|', $html) . ")\b([^>]*>)~i", $str, $c)) {
            if ($count)
                return array(true, count($c[0]));
            else
                return true;
        } else {
            return false;
        }
    }

    /**
     * takes a string of text and retrieves
     * a filtered list of keywords within the text
     *
     * @param   string   $string             the text to be filtered
     * @param   array    $common_words_array array of common words to be stripped
     * @param int|string $length             the number of words to be included
     * @param int|string $word_length        the string length of the words to be included
     * @param   string   $delim              the item being used as delimiter on the string
     *
     * @return    string
     */
    public static function make_page_keywords($string, $common_words_array, $length = 30, $word_length = 3, $delim = ",")
    {

        $keywords = array();

        // remove the content in between the STYLE tags
        $string = preg_replace("'<style[^>]*>.*</style>'siU", ' ', $string);

        // remove the content in between the SCRIPT tags
        $string = preg_replace("~<script[^>]*>.+</script[^>]*>~isU", ' ', $string);

        //an array of the HTML entities
        $search = array("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i", "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i");

        // what we're replacing HTML entities with
        $replace = array(" ", " ", " ", " ", " ", " ", " ", " ", " ");

        // do the work to strip the HTML entities
        $string = preg_replace($search, $replace, $string);

        // now strip all the tags
        $final_output = strip_tags($string);

        // remove everything that isn't a letter
        $final_output = ereg_replace("[^A-Za-z ]", " ", $final_output);

        //put the words into an array
        $text_array = explode(" ", $final_output);

        // loop through the article AND the common words, generating a new
        // array only consisting of the words that arent in both arrays
        for ($i = 0; $i < count($text_array); $i++) {
            if (((array_search(strtolower($text_array[$i]), $common_words_array)) == false) && (strlen($text_array[$i]) >= $word_length)) {
                $keywords[] = $text_array[$i];
            }
        }

        $occurence = array_count_values($keywords);

        arsort($occurence);

        $top_words = array_slice(array_keys($occurence), 0, $length);

        $final_keywords = implode($delim, $top_words);

        return $final_keywords;
    }

}