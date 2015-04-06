<?php

/**
 * Class emailTemplate
 */
class emailTemplate
{

    protected $path, $tags, $template;

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
        $this->template = "";
        $this->defaults = $defaults;
    }

    /**
     * Destructor. Un-sets all object variables
     */
    public function __destruct()
    {
        $vars = get_object_vars($this);
        if (is_array($vars)) {
            foreach ($vars as $key => $val) {
                $this->$key = null;
            }
        }
    }

    /**
     *
     * Sets the tags to be used to populate template data
     * This can be used on the tags fed via constructor or new tags can be added, too
     *
     * @param   array $tags associative array of key=>val pairs
     *
     * @return  bool
     */
    public function setTags($tags = null)
    {
        // if the $tags argument isn't null, we're passing in some new tags
        if (!is_null($tags)) {
            if (is_array($tags)) {
                foreach ($tags AS $key => $val) {
                    $this->tags[$key] = $val;
                }

                return true;
            }
        } else {
            // in this case, we're passing in the tags from the constructor
            if (!is_null($this->tags)) {
                if (is_array($this->tags)) {
                    foreach ($this->tags AS $key => $val) {
                        $this->tags[$key] = $val;
                    }
                }

                return true;
            }
        }
    }

    /**
     * wipes out the tags array so we can set them to new values
     */
    public function clearTags()
    {
        unset($this->tags);
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
        }
    }

    /**
     * function to unset the template file so we can reset it to something else
     */
    public function unload()
    {
        unset($this->template);
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

            return true;
        }
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
     * Sends the email
     *
     * @param   string $to     who is getting the email
     * @param   string $subj   the email subject
     * @param   string $sEmail sender email
     * @param   string $sName  sender name
     *
     * @return   bool
     */
    public function send($to, $subj, $sEmail, $sName)
    {
        $msg = $this->parse();

        return Network::gEmail($to, $subj, $msg, $sEmail, $sName);
    }
}