<?php

/**
 * Class template
 */
class template
{

    private $path, $tags, $template;

    /**
     * class constructor
     *
     * @param   string $path     path to folder where templates are located
     * @param   array  $tags     key=>val pairs of tags and their content
     * @param   array  $defaults array of default values for template tags
     */
    public function __construct($path, $tags = null, $defaults = null)
    {
        $this->tags = $tags;
        $this->path = $path;
        $this->template = '';
        $this->defaults = $defaults;
    }

    /**
     *
     * Sets the tags to be used to populate template data
     * This can be used on the tags fed via constructor or new tags can be added, too
     *
     * @param   array $tags associative array of key=>val pairs
     */
    public function setTags($tags = null)
    {

        // if the $tags argument isn't null, we're passing in some new tags
        if (!is_null($tags)) {
            if (is_array($tags)) {
                foreach ($tags AS $key => $val) {
                    $this->tags[$key] = $val;
                }
            }
        } else {
            // in this case, we're passing in the tags from the constructor
            if (!is_null($this->tags)) {
                if (is_array($this->tags)) {
                    foreach ($this->tags AS $key => $val) {
                        $this->tags[$key] = $val;
                    }
                }
            }
        }
    }

    /**
     *
     * getter
     *
     * @param   mixed $thing the thing we're getting
     *
     * @return  mixed
     */
    public function __get($thing)
    {
        if (isset($this->$thing)) {
            return $this->$thing;
        }

        return false;
    }

    /**
     *
     * setter
     *
     * @param   string $thing
     * @param   string $value
     */
    public function __set($thing, $value)
    {
        $this->$thing = $value;
    }

    /**
     *
     * Loads a template file and turns it into a string
     *
     * @param   string $file name of the template file
     *
     * @return  bool
     */
    public function load($file)
    {
        try {
            if (isset($file) && file_exists($this->path . $file)) {
                $this->template = file_get_contents($this->path . $file);
            } else {
                throw new gException("Could not load template file");
            }
        } catch (gException $e) {
            echo $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * sets missing tags to their default values
     */
    public function setDefaults()
    {
        if (!is_array($this->defaults)) {
            return '';
        }
        foreach ($this->defaults AS $k => $v) {
            if (!in_array($k, $this->tags)) {
                $this->tags[$k] = $v;
            }
        }

        return true;
    }

    /**
     *
     * parses the template file and replaces tags with their content
     */
    public function parse()
    {
        $this->setDefaults();
        if (is_array($this->tags)) {
            foreach ($this->tags as $key => $value) {
                $this->template = str_replace("{{" . $key . "}}", $value, $this->template);
            }
        }

        return $this->template;
    }

    /**
     *
     * Generates meta tags
     *
     * @param   array  $array
     * @param   string $return what to return. Options are only 'string' or 'array'
     *
     * @return  array|string
     */
    public function setMetaTags($array, $return = 'string')
    {
        $output = null;

        $equiv = array('cache-control', 'content-type', 'pics-label', 'pragma', 'refresh', 'expires');

        foreach ($array AS $key => $val) {
            if (in_array(strtolower($key), $equiv)) {
                $output[] = '<meta http-equiv="' . $key . '" content="' . $val . '" />';
            } else {
                $output[] = '<meta name="' . $key . '" content="' . $val . '" />';
            }
        }
        if (strtoupper($return) == 'STRING') {
            if (is_array($output)) {
                return implode("\n", $output);
            } else {
                return $output;
            }
        } else {
            return $output;
        }
    }

    /**
     *
     * Generates links to external CSS or Javascript assets
     *
     * @param   array|string $path   path(s) to the assets
     * @param   string       $type   what type of assets these are. Options are only CSS or Javascript
     * @param   string       $return what to return. Options are only 'string' or 'array'
     *
     * @return  array|string
     */
    public function setAssets($path, $type, $return = 'string')
    {
        $output = null;

        // Stylesheets
        if (strtoupper($type) == 'CSS') {
            if (is_array($path)) {
                foreach ($path AS $val) {
                    $output[] = '<link rel="stylesheet" href="' . $val . '" />';
                }
            } else {
                $output[] = '<link rel="stylesheet" href="' . $path . '" />';
            }
        } // Javascript
        elseif (strtoupper($type) == 'JAVASCRIPT') {
            if (is_array($path)) {
                foreach ($path AS $val) {
                    $output[] = "<script src=" . $val . "></script>";
                }
            } else {
                $output[] = "<script src=" . $path . "></script>";
            }

        } // sanity check. If not CSS or Javascript, then what the fuck?
        else {
            return false;
        }

        // determine what to return
        if (strtoupper($return) == 'STRING') {
            if (is_array($output)) {
                return implode("\n", $output);
            } else {
                return $output;
            }
        } else {
            return $output;
        }
    }

    /**
     *
     * Loads a file of static content into a string
     *
     * @param   string $file full system path to the file
     *
     * @return  string
     */
    public function getStaticContent($file)
    {
        try {
            if (isset($file) && file_exists($file)) {
                return file_get_contents($file);
            } else {
                throw new gException("Could not load content file");
            }
        } catch (gException $e) {
            echo $e->getMessage();

            return false;
        }
    }

    /**
     *
     * returns content only if a specific condition is true. else, returns a blank string
     *
     * @param   $condition
     * @param   $content
     * @param   $alternative
     *
     * @return  string
     */
    public function conditionalDisplay($condition, $content, $alternative = '')
    {
        if (false === $condition) {
            return $alternative;
        } else {
            return $content;
        }
    }
}