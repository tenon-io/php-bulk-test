<?php

/**
 *
 * This class is used to present & validate forms.  Processing of the data is,
 * for the most part, not handled by this class.  A very simple processing to submit into
 * a database is available via the processSimple method but anything more complex
 * should be done elsewhere.
 *
 * @todo create method to use multidimensional array in a SELECT element
 * @todo create method to make nested checkboxes via nested OLs
 * @todo create method to make two-column checkbox options
 * @todo prevent consumer idiocy by checking for 'value' as a key on $inputAttrs and stripping it
 *
 */
class gForm
{

    public $action; //path to the script to process the form
    public $method; //method attribute value
    public $connection; // connection details for database. required for database-driven validation options
    public $config; // array of configuration options
    public $presets; // preset values for the form elements
    public $valfailed; // array of items which have failed validation
    public $valmessage; // array of validation messages
    protected $_output; // the generated HTML output for the form

    /**
     *
     * @param   string $action
     * @param   string $method
     * @param null     $connection
     */
    public function __construct($action = '', $method = '', $connection = null)
    {
        // the form's 'action' attribute. defaults to current URL
        $this->action = (strlen($action) > 1) ? $action : $_SERVER['REQUEST_URI'];

        // the form's 'method' attribute. defaults to post
        $this->method = (strlen($method) > 1) ? $method : "post";

        // array holding database connection details
        $this->connection = $connection;

        $this->valfailed = false;

        //initiate the output
        $this->_output = '';
    }

    /**
     * Destructor. Unsets all object variables
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
     * Sets the configuration options for use in the other methods in the class
     * See gFromConfig.php file for options and examples
     *
     * @param   array $array
     */
    public function setConfigOpts($array)
    {
        foreach ($array AS $key => $val) {
            $this->config[$key] = $val;
        }
    }

    /**
     *
     * adds a value to the presets array
     *
     * @param   string $key
     * @param   mixed  $val
     */
    public function addPreset($key, $val)
    {
        $this->presets[$key] = $val;
    }

    /**
     *
     * This sets the values for all of the elements on the form using an associative array
     * the key for each item in the array needs to match the 'name' attribute for each element
     *
     * @param   array $array
     */
    public function setPresets($array)
    {
        foreach ($array AS $key => $val) {
            $this->addPreset($key, $val);
        }
    }

    /**
     *
     * Takes an associative array of attributes (and their values) and turns it into a string for use in the relevant element.
     *
     * @param   array $attrs
     *
     * @return  string
     */
    protected function loopAttrs($attrs = null)
    {
        $output = '';
        if (!is_null($attrs)) {
            foreach ($attrs AS $k => $v) {
                $output .= " $k=\"$v\" ";
            }

            return $output;
        }

        return '';
    }

    /**
     *
     * @param   string $newClass
     * @param   array  $attrs
     */
    protected function appendClassAttr($newClass, &$attrs)
    {
        if (!is_null($attrs)) {
            if (!isset($attrs['class'])) {
                $attrs['class'] = $newClass;
            } else {
                $attrs['class'] = $attrs['class'] . " $newClass";
            }
        } else {
            $attrs['class'] = $newClass;
        }
    }

    /**
     *
     * Ensures that if there's no type supplied, it is set to 'text'.
     * Necessary for certain HTML5 magic
     *
     * @param array $attrs
     */
    protected function enforceInputType(&$attrs)
    {
        if (!is_null($attrs)) {
            if (!isset($attrs['type'])) {
                $attrs['type'] = 'text';
            }
        } else {
            $attrs['type'] = 'text';
        }
    }

    /**
     *
     * This method helps to ensure that if the required attribute is not already
     * set in the attributes array that it gets added. Also sets aria-required
     * at the same time.
     */
    protected function setRequiredAttr(&$attrs)
    {
        /**
         * We only do a very simple check here that its set.
         * There is no validation that the value is correct as we assume
         * if you screw that up then there's a reason for you to have done so.
         */
        if (!isset($attrs['aria-required'])) {
            $attrs['aria-required'] = "true";
        }
        if (!isset($attrs['required'])) {
            $attrs['required'] = "required";
        }
    }

    /**
     *
     * Does VERY simple processing of form data to insert the information into a database table
     * Created as a convenience factor, really, since 99% of what I use forms for is exactly
     * this sort of thing.
     *
     * @param   array  $data
     * @param   string $table
     *
     * @return  bool
     */
    public function processSimple($data, $table)
    {

        $db = new dbPDO($this->connection['dbName'],
            $this->connection['user'],
            $this->connection['pass'],
            $this->connection['opts'],
            $this->connection['dbType'],
            $this->connection['hostORpath'],
            $this->connection['port']);

        return $db->InsertArray($data, $table);
    }

    /**
     *
     * Does a REALLY simple processing of form data and sends it via e-mail
     *
     * @param   mixed  $data
     * @param   string $subject
     * @param   string $stripFromKey
     * @param   string $recipient
     * @param   string $sender
     *
     * @return  bool
     */
    public function simpleEmailProcess($data, $subject, $stripFromKey = null, $recipient = null, $sender = null)
    {
        $message = '';

        if (is_null($recipient)) {
            $recipient = $this->config['default_email_recipient'];
        }
        if (is_null($sender)) {
            $sender = $this->config['default_email_sender'];
        }

        // assemble message for the e-mail
        // quick sanity check to ensure $data is an array before looping
        if (is_array($data)) {
            foreach ($data AS $key => $val) {

                // strip delimiter from $data array keys
                // for instance, 'user_name' becomes 'user name'
                if (!is_null($stripFromKey)) {
                    $key = str_replace($stripFromKey, ' ', $key);
                }

                // make teh message
                $message .= "$key : $val \n";
            }
        } // $data not an array? Treat it like a string
        // GOTCHA: the gFormEmail method only supports plain text,
        // so don't get tricky and try putting HTML in here
        else {
            $message = $data;
        }

        // send it, baby!
        // @TODO - this violates DRY. Use network:: instead
        return gFormUtils::gFormEmail($recipient, $sender, $subject, $message);
    }

    /**
     *
     * Used as a sanity check: make sure an ID was set so we can refer to it in the label
     *
     * @param   string $string
     * @param   array  $attrs
     *
     * @return  string
     */
    protected function setElementID($string, $attrs = null)
    {
        if (isset($attrs['id'])) {
            $attrs['id'] = Strings::stripNonAlphanumeric($attrs['id'], false);
        } else {
            $attrs['id'] = Strings::stripNonAlphanumeric($string, false);
        }

        return $attrs;
    }

    /**
     *
     * Sets the value of an arbitrary attribute
     *
     * @param   string $attrName
     * @param   string $value
     * @param   array  $attrs
     * @param   string $append :GOTCHA really misleading variable name
     */
    public function setAttr($attrName, $value, &$attrs, $append = null)
    {
        if (!is_null($attrs)) {
            if (isset($attrs[$attrName])) {
                // The $append parameter actually lets us prepend, append, or leave the attribute alone
                // if NULL, leave the attribute value alone
                if ($append == null) {
                    $attrs[$attrName] = Strings::stripNonAlphanumeric($attrs[$attrName], false);
                    // if $append is set to 'append', attach the value to the end of the attribute
                } elseif ($append == 'append') {
                    $attrs[$attrName] = Strings::stripNonAlphanumeric($attrs[$attrName], false) . " $value";
                    // if $append is set to 'prepend', put it on the beginning
                } elseif ($append == 'prepend') {
                    $attrs[$attrName] = "$value " . Strings::stripNonAlphanumeric($attrs[$attrName], false);
                } else {
                    $attrs[$attrName] = Strings::stripNonAlphanumeric($attrs[$attrName], false);
                }
            } else {
                $attrs[$attrName] = Strings::stripNonAlphanumeric($value, false);
            }
        } else {
            $attrs[$attrName] = Strings::stripNonAlphanumeric($value, false);
        }

    }

    /**
     *
     * gets the beginning of a form
     *
     * @param   string $pretext   arbitrary string of stuff to put before the form (such as instructions)
     * @param   array  $formAttrs attributes for the FORM element
     * @param   array  $wrapAttrs attributes for whatever the wrapper element is
     * @param   string $extras    arbitrary string of stuff (such as javascript) for the FORM element
     */
    public function formStart($pretext = '', $formAttrs = null, $wrapAttrs = null, $extras = '')
    {
        $output = '';
        $output .= $pretext;

        $output .= "<div " . $this->loopAttrs($wrapAttrs) . ">\n";
        $output .= "<form action=\"" . $this->action . "\" method=\"" . $this->method . "\"" . $this->loopAttrs($formAttrs) . $extras . ">\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * creates text (or anything) inside a form, helps avoid breaking the form
     * especially when the form is laid out in a table
     *
     * @param    string $string the stuff being printed
     * @param null      $attrs
     * @param   bool    $nowrap allows for the string to be included raw with no wrapping stuff.
     *                          typically a bad idea, but adds flexibility (i.e. including datalists)
     */
    public function formExp($string, $attrs = null, $nowrap = false)
    {
        // nowrap allows string to be included without a wrapper
        if ($nowrap == true) {
            $this->_output .= $string;
        } else {
            $output = '';
            $output .= "<div " . $this->loopAttrs($attrs) . ">$string</div>\n";

            $this->addToDisplay($output);
        }
    }

    /**
     *
     * creates the end of a form. Creates the form buttons and closes the wrapper
     *
     * @param   string $submitName
     * @param   string $submitValue
     * @param   string $cancelValue
     * @param   array  $wrapperAttrs
     * @param null     $submitAttrs
     * @param null     $cancelAttrs
     */
    public function formEnd($submitName = null, $submitValue = null, $cancelValue = null, $wrapperAttrs = null, $submitAttrs = null, $cancelAttrs = null)
    {
        $output = '';

        $output .= $this->submit($submitName, $submitValue, $cancelValue, $wrapperAttrs, $submitAttrs, $cancelAttrs);
        $output .= "</form>\n";
        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * creates a user friendly warning message that one or more items failed validation
     * this is intended to go above the entire form to serve as a global message
     *
     * @param   string $msg   the validation warning message
     * @param   array  $attrs attributes for the DIV that holds the message
     */
    public function formWarn($msg = null, $attrs = null)
    {

        // if validation has not failed, or if the message is empty return nothing
        if ((!is_array($this->valfailed)) || (false == $this->valfailed)) {
            return;
        }

        if (!isset($msg)) {
            $msg = $this->config['form_warning_msg'];
        }
        $output = '';
        $output .= "<div" . $this->loopAttrs($attrs) . ">\n" . $msg;

        if ($this->config['list_errors'] == true) {
            if (is_array($this->valmessage)) {
                $output .= "<ul>\n";

                foreach ($this->valmessage AS $message) {
                    $output .= "<li>$message</li>\n";
                }

                $output .= "</ul>\n";
            }
        }

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * creates an INPUT element. If no 'type' value is supplied in input attrs, defaults to text
     *
     * @param   string $label
     * @param   string $varname
     * @param   bool   $required
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     * @param   string $postString
     */
    public function editbox($label, $varname, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '')
    {
        $output = '';

        //make an ID for this element
        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        //ensure there's a 'type' attribute. default to 'text' if not;
        $this->enforceInputType($inputAttrs);

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            //add the element error class onto the inputAttrs array value for 'class'
            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div " . $this->loopAttrs($wrapperAttrs) . ">\n";

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span>\n";

        $output .= "<input name=\"$varname\" ";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        $output .= $this->loopAttrs($inputAttrs);

        /**
         * GOTCHA: this strips slashes and converts HTML entities for security and stability
         * GOTCHA: setting the value attribute in the $inputAttrs param will cause bugs, so don't do that
         *          instead use the setPresets method to do that
         */
        if ($this->presets[$varname]) {
            $output .= "value=\"" . stripslashes($this->presets[$varname]) . "\"";
        }

        $output .= " />\n";
        $output .= $postString;

        $output .= "</label></div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * SELECT ELEMENT (normal)
     * Prints an HTML <select> element and all its related <option> elements
     * In this case, the <option> elements utilize an array which has no associative keys.
     *
     * @param   string $label
     * @param   string $varname
     * @param   array  $value_array
     * @param   bool   $required
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     * @param   string $postString
     */
    public function dropdown($label, $varname, $value_array, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '')
    {
        $output = '';

        //make an ID for this element
        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div " . $this->loopAttrs($wrapperAttrs) . ">\n";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span>\n";

        $output .= "<select name=\"$varname\" " . $this->loopAttrs($inputAttrs) . ">\n";

        foreach ($value_array as $item) {
            if ($item == $this->presets[$varname])
                $selected = " selected=\"selected\" ";
            else
                $selected = "";
            $output .= "<option value=\"$item\"" . $selected . ">$item</option>\n";
        }

        $output .= "</select>\n";
        $output .= $postString;
        $output .= "</label></div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * SELECT ELEMENT - uses the pipe method for different values than labels
     * uses an array like 'item1|1' and makes '<option value="1">item1</option>'
     * prints an HTML <select> element, with all its related <options>
     * different from above in the manner which it handles the option values
     *
     * @param   string $label
     * @param   string $varname
     * @param   array  $value_array
     * @param   bool   $required
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     * @param   string $postString
     */
    public function dropdown_val($label, $varname, $value_array, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '')
    {
        $output = '';

        //make an ID for this element
        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {

            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            //add the element error class onto the inputAttrs array value for 'class'
            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div " . $this->loopAttrs($wrapperAttrs) . ">\n";

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span>\n";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        $output .= "<select name=\"$varname\" " . $this->loopAttrs($inputAttrs) . ">\n";

        foreach ($value_array as $item) {
            list ($item_label, $item_value) = explode("|", $item);
            if ($item_value == $this->presets[$varname])
                $selected = " selected ";
            else
                $selected = "";
            $output .= "<option value=\"$item_value\"" . $selected . ">$item_label</option>\n";
        }

        $output .= "</select>\n";
        $output .= $postString;

        $output .= "</label></div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * SELECT ELEMENT - STATE
     * creates a dropdown for states (US States and Canadian Provinces are supported)
     *
     * @param   string $label
     * @param   string $varname
     * @param   bool   $required
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     * @param bool     $us_only
     * @param   string $postString
     */
    public function stateDropdown($label, $varname, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $us_only = false, $postString = '')
    {
        $output = '';

        $us_only = (strlen($us_only) > 1) ? $us_only : false;

        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////
        $output .= "<div " . $this->loopAttrs($wrapperAttrs) . ">\n";

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span>\n";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        $output .= "<select name=\"$varname\" " . $this->loopAttrs($inputAttrs) . ">\n";
        $output .= "<option value=\"\">Select State</option>\n";

        if ($us_only == false) {
            $output .= "<optgroup label=\"United States\">\n";
        }

        foreach ($this->config['us_states_array'] as $item) {
            if ($item == $this->presets[$varname])
                $selected = " selected=\"selected\" ";
            else
                $selected = "";
            $output .= "<option value=\"$item\"" . $selected . ">$item</option>\n";
        }

        if ($us_only == false) {
            $output .= "</optgroup>\n";
        }

        if ($us_only == false) {
            $output .= "<optgroup label=\"Canada\">\n";

            foreach ($this->config['canada_provinces_array'] as $item) {
                if ($item == $this->presets[$varname])
                    $selected = " selected=\"selected\" ";
                else
                    $selected = "";
                $output .= "<option value=\"$item\"" . $selected . ">$item</option>\n";
            }

            $output .= "</optgroup>\n";
        }

        $output .= "</select>\n";
        $output .= $postString;
        $output .= "</label></div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * SELECT ELEMENT - DATE
     * This presents a SET of dropdowns for date
     * This is based on HTML4 and does not do any of the new HTML5 hotness
     * this creates a set of three dropdowns for month, day, and year
     *
     * @todo     there appears to be no way to add inputAttrs to the elements
     *
     * @param   string $label                                       the text label (for the entire set
     * @param    array $settings                                    array of settings with the following keys
     *                                                              array    $daydrop   settings for use in creating the day
     *                                                              dropdown
     *                                                              array    $monthdrop settings for use in creating the month
     *                                                              dropdown
     *                                                              array    $yeardrop  settings for use in creating the year
     *                                                              dropdown
     *                                                              string    $varname            name of this element set, for use in
     *                                                              printing validation warnings
     *                                                              string    $order              pipe delimited list, used to order these
     *                                                              elements
     *                                                              MUST be pipe-delimited and MUST contain
     *                                                              ONLY the words "month", "day", and "year",
     *                                                              separated by pipes
     * @param          $varname
     * @param   array  $wrapperAttrs                                array of attributes for the wrapper
     * @param   array  $labelAttrs                                  array of attributes for the labels
     * @param   string $postString
     *
     * @internal param array $inputAttrs array of attributes for the inputs
     * @return bool
     */
    public function dateDropSet($label, $settings, $varname, $wrapperAttrs = null, $labelAttrs = null, $postString = '')
    {
        $output = '';

        $daydrop = $settings['daydrop'];
        $monthdrop = $settings['monthdrop'];
        $yeardrop = $settings['yeardrop'];

        // sanity check - make sure if the order isn't set that it is set to something sensible
        if (!isset($settings['order'])) {
            $settings['order'] = "month|day|year";
        }

        $order = $settings['order'];

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">$label \n";

        // get the output from the three dropdowns
        if (isset($settings['daydrop'])) {
            $day = $this->daydrop($daydrop);
        }
        if (isset($settings['monthdrop'])) {
            $month = $this->monthdrop($monthdrop);
        }
        if (isset($settings['yeardrop'])) {
            $year = $this->yeardrop($yeardrop);
        }

        // Get the order we want them in
        $order_array = explode("|", $order);

        // now, basically, we're sorting them and then putting
        // them into their final place in the output
        // for example, if the order is "Month, Day, Year" then
        // this loops through the order and puts the generated
        // dropdowns in that order. First the month dropdown, then the day, then the year
        foreach ($order_array AS $val) {
            $output .= ${$val};
        }

        $output .= $postString;

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * MULTI SELECT ELEMENT
     * Creates a multi-select element.
     * Keep in mind, this kind of form element has inherent accessibility challenges
     * so a series of checkboxes may be a better option
     *
     * @param   string $label
     * @param   string $varname
     * @param   array  $value_array
     * @param   bool   $required
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     * @param   string $postString
     */
    public function multiSelect($label, $varname, $value_array, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '')
    {
        $output = '';

        //make an ID for this element
        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////
        // Start $x as zero - used to increment the label/ id pairs to retain
        // unique values
        $x = 0;

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span>\n";

        $output .= "<select name=\"$varname\" multiple=\"multiple\" " . $this->loopAttrs($inputAttrs) . ">";

        foreach ($value_array as $item) {
            // increment the $x variable each time
            $x++;

            if ($method == "pipe") {
                list ($item_label, $item_value, $item_extras) = explode("|", $item);

                if (is_array($this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                    if (in_array($item_value, $this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                        $selected = " selected=\"selected\" ";
                    } else {
                        $selected = "";
                    }
                }
            } else {
                $item_value = $item;
                $item_label = $item;

                if (is_array($this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                    if (in_array($item_value, $this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                        $selected = " selected=\"selected\" ";
                    } else {
                        $selected = "";
                    }
                }
            }

            $output .= "<option value=\"$item_value\"" . $selected . ">$item_label</option>";
        }
        $output .= "</select>\n";
        $output .= $postString;

        $output .= "</label></div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * TEXTAREA ELEMENT
     * makes a TEXTAREA element
     *
     * @param   string $label
     * @param   string $varname
     * @param   bool   $required
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     * @param   string $postString
     */
    public function textarea($label, $varname, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '')
    {
        $output = '';

        //make an ID for this element
        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";

        $output .= "<label for=\"" . $inputAttrs['id'] . "\"" . $this->loopAttrs($labelAttrs) . "><span>$label</span>\n";

        $output .= "<textarea name=\"$varname\" ";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        $output .= "" . $this->loopAttrs($inputAttrs) . ">" . stripslashes(htmlentities($this->presets[$varname])) . "</textarea>\n";

        $output .= $postString;

        $output .= "</label></div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * HIDDEN FORM ELEMENT
     * Creates a hidden form element
     *
     * @todo this method doesn't actually support any of the $inputAttrs, $wrapperAttrs, etc.
     *
     * @param   string $varname
     * @param   string $varvalue
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     */
    public function hiddenbox($varname, $varvalue, $inputAttrs = null, $wrapperAttrs = null)
    {
        $output = '';

        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);
        }

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";

        // GOTCHA: the value for $this->presets[$varname] may override the declared
        // value for $varvalue.  It shouldn't, but can, so be careful
        if (isset($this->presets[$varname])) {
            $varvalue = $this->presets[$varname];
        }

        $output .= "<input type=\"hidden\" name=\"$varname\" ";

        $output .= $this->loopAttrs($inputAttrs);

        $output .= " value=\"$varvalue\">";

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * CHECKBOX ELEMENT
     * creates a set of checkboxes
     *
     * @param   string $varname
     * @param   array  $value_array
     * @param   string $method
     * @param   string $display
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     */
    public function checkbox($varname, $value_array, $method = null, $display = "block", $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null)
    {
        $output = '';

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";

        if ($display == "inline") {
            $output .= "<div>\n";
        }

        // instantiate $x for use in creating unique IDs for the checkboxes
        $x = 0;
        foreach ($value_array as $item) {
            // increment the $x variable each time
            $x++;

            // the element ID is the element name with the $x value appended to it
            $var_id = Strings::stripNonAlphanumeric($varname . $x, false);

            if ($method == "pipe") {
                list ($item_label, $item_value, $item_extras) = explode("|", $item);

                if (is_array($this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                    if (in_array($item_value, $this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                        $selected = " checked=\"checked\" ";
                    } else {
                        $selected = "";
                    }
                }
            } else {
                $item_value = $item;
                $item_label = $item;

                if (is_array($this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                    if (in_array($item_value, $this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                        $selected = " checked=\"checked\" ";
                    } else {
                        $selected = "";
                    }
                }
            }


            if ($display == "block") {
                $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";
            } elseif ($display == "inline") {
                //any CSS foo necessary for these spans should be handled through descendent selectors
                $output .= "<span>\n";
            }


            $output .= "<label for=\"$var_id\"" . $this->loopAttrs($labelAttrs) . ">";

            $output .= "<input type=\"checkbox\" name=\"$varname\" id=\"$var_id\" value=\"$item_value\"";

            $output .= $selected . $this->loopAttrs($inputAttrs) . ">\n";

            $output .= "<span>$item_label</span></label>\n";

            if ($display == "block") {
                $output .= "</div>\n";
            } elseif ($display == "inline") {
                $output .= "</span>\n";
            }
        }
        if ($display == "inline") {
            $output .= "</div>\n";
        }

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }


    /**
     *
     * CHECKBOX ELEMENTS - NESTED
     * creates a set of checkboxes as a nested list to denote hierarchy
     *
     * @todo    I don't think this method is finished
     *
     * @param   string $varname
     * @param   array  $value_array
     * @param   string $method
     * @param   string $display
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     */
    public function checkboxNested($varname, $value_array, $method = null, $display = "block", $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null)
    {
        $output = '';

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";

        if ($display == "inline") {
            $output .= "<div>\n";
        }

        // instantiate $x for use in creating unique IDs for the checkboxes
        $x = 0;
        foreach ($value_array as $item) {
            // increment the $x variable each time
            $x++;

            // the element ID is the element name with the $x value appended to it
            $var_id = Strings::stripNonAlphanumeric($varname . $x, false);

            if ($method == "pipe") {
                list ($item_label, $item_value, $item_extras) = explode("|", $item);


                if (is_array($this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                    if (in_array($item_value, $this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                        $selected = " checked=\"checked\" ";
                    } else {
                        $selected = "";
                    }
                }
            } else {
                $item_value = $item;
                $item_label = $item;

                if (is_array($this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                    if (in_array($item_value, $this->presets[Strings::stripNonAlphanumeric(trim($varname))])) {
                        $selected = " checked=\"checked\" ";
                    } else {
                        $selected = "";
                    }
                }
            }


            if ($display == "block") {
                $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";
            } elseif ($display == "inline") {
                //any CSS foo necessary for these spans should be handled through descendent selectors
                $output .= "<span>\n";
            }

            $output .= "<label for=\"$var_id\"" . $this->loopAttrs($labelAttrs) . ">";

            $output .= "<input type=\"checkbox\" name=\"$varname\" id=\"$var_id\" value=\"$item_value\"";

            $output .= $selected . $this->loopAttrs($inputAttrs) . ">\n";

            $output .= "<span>$item_label</span></label>\n";

            if ($display == "block") {
                $output .= "</div>\n";
            } elseif ($display == "inline") {
                $output .= "</span>\n";
            }
        }
        if ($display == "inline") {
            $output .= "</div>\n";
        }

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }


#########################################################################
# FIELDSET & LEGEND ELEMENTS - 2 Functions, to begin and end the fieldset
#########################################################################

    /**
     *
     * used to open a fieldset
     *
     * @param   string $legend_label
     * @param   array  $fieldsetAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $legendAttrs
     */
    public function fieldsetStart($legend_label, $fieldsetAttrs = null, $wrapperAttrs = null, $legendAttrs = null)
    {
        $output = '';

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";
        $output .= "<fieldset " . $this->loopAttrs($fieldsetAttrs) . ">\n";
        $output .= "<legend " . $this->loopAttrs($legendAttrs) . ">$legend_label</legend>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * used to end a fieldset
     */
    public function fieldsetEnd()
    {
        $output = '';

        $output .= "</fieldset>\n";
        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * RADIO BUTTON ELEMENT
     * creates a set of radio buttons
     *
     * @param   string $varname
     * @param   array  $valueArray
     * @param   string $method
     * @param   string $display
     * @param   array  $inputAttrs
     * @param   array  $wrapperAttrs
     * @param   array  $labelAttrs
     */
    public function radioButton($varname, $valueArray, $method = null, $display = 'block', $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null)
    {
        $output = '';

        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }

        /////////////////////////////////////////////////////////////////////
        // Start $x as zero - used to increment the label/ id pairs to retain
        // unique values
        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";

        if ($display == "inline") {
            $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";
        }

        $x = 0;
        foreach ($valueArray as $item) {
            // increment the $x variable each time
            $x++;

            // the element ID is the element name with the $x value appended to it
            $var_id = Strings::stripNonAlphanumeric($varname . $x, false);

            if ($method == "pipe") {
                list ($item_label, $item_value, $item_extras) = explode("|", $item);

                if (is_array($this->presets)) {
                    if ((string)trim($item_value) == (string)$this->presets[Strings::stripNonAlphanumeric(trim($varname))]) {
                        $checked = " checked=\"checked\" ";
                    } else {
                        $checked = '';
                    }
                }
            } else {
                $item_value = $item;
                $item_label = $item;

                if (is_array($this->presets)) {
                    if ((string)trim($item_value) == (string)$this->presets[Strings::stripNonAlphanumeric(trim($varname))]) {
                        $checked = " checked=\"checked\" ";
                    } else {
                        $checked = '';
                    }
                }

                if ($item_value == $this->presets[$varname]) {
                }
            }


            if ($display == "block") {
                $output .= "<div>\n";
            } elseif ($display == "inline") {
                $output .= "<span>\n";
            }

            $output .= "<label for=\"$var_id\"" . $this->loopAttrs($labelAttrs) . ">";

            $output .= "<input type=\"radio\" name=\"$varname\" id=\"$var_id\" value=\"$item_value\"";

            $output .= $this->loopAttrs($inputAttrs) . $checked . ">\n";

            $output .= "<span>$item_label</span></label>\n";

            if ($display == "block") {
                $output .= "</div>\n";
            } elseif ($display == "inline") {
                $output .= "</span>\n";
            }
        }

        if ($display == "inline") {
            $output .= "</div>\n";
        }

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     *
     * SUBMIT & CANCEL BUTTONS
     * Creates the submit and cancel buttons
     *
     * @param   string $submitName
     * @param   string $submitValue
     * @param   string $cancelValue
     * @param   array  $wrapperAttrs
     * @param null     $submitAttrs
     * @param null     $cancelAttrs
     */
    public function submit($submitName = null, $submitValue = null, $cancelValue = null, $wrapperAttrs = null, $submitAttrs = null, $cancelAttrs = null)
    {
        $output = '';

        $submitName = (strlen($submitName) > 1) ? $submitName : $this->config['default_submit_name'];
        $submitValue = (strlen($submitValue) > 1) ? $submitValue : $this->config['default_submit_value'];

        if (false != $cancelValue) {
            $cancelValue = (strlen($cancelValue) > 1) ? $cancelValue : $this->config['default_cancel_value'];
        } else {
            $cancelValue = null;
        }

        $output .= "<div" . $this->loopAttrs($wrapperAttrs) . ">\n";
        $output .= "<input type=\"submit\" name=\"$submitName\" value=\"$submitValue\" " . $this->loopAttrs($submitAttrs) . ">\n";

        if ((!is_null($cancelValue)) && (false != $cancelValue)) {
            $output .= "<input type=\"button\" value=\"$cancelValue\" " . $this->loopAttrs($cancelAttrs) . ">\n";
        }

        $output .= "</div>\n";

        $this->addToDisplay($output);
    }

    /**
     * REQUIRED ITEM SNIPPET
     * returns an HTML snippet to mark an item as required
     *
     * @param    none
     */
    public function req()
    {
        return $this->config['reqd_snippet'];
    }

    /**
     * prints the validation error message above each form element
     *
     * @param $message
     * @param $varname
     *
     * @return string
     */
    public function valError($message, $varname)
    {
        $output = '';

        $this->config['valError_msg_attrs']['id'] = "error" . Strings::stripNonAlphanumeric(trim($varname));

        $output .= "<div " . $this->loopAttrs($this->config['valError_msg_attrs']) . ">\n";

        $output .= "$message\n";

        $output .= "</div>\n";

        return $output;
    }

################################################################################
# VALIDATE FUNCTION - validates against one of many choices described below
# Types:
# 0 = strlen > 2  - $var must be longer than 2 characters long
# 1 = email  - $var must be a valid e-mail address
# 2 = isset  - $var must be set to SOMETHING
# 3 = Select One - $var cannot be "Select One"
# 4 = numbers only - $var must ONLY be numbers
# 5 = alphanumeric only - $var must be numbers or letters
# 6 = date - $var cannot be "Month", "Day", or "Year"
# 7 = state - $var cannot be "Select State"
# 8 = money - $var must be a money format 'xxx.xx'
# 9 = strlen > $param - $var must at least be $param characters long
# 10 = value should not match $param
# 11 = value should not contain new line characters
# 12 = value must correspond to something in the $param array
# 13 = value must equal a predefined string
# 14 = value must NOT already exist in the database
# 15 = value must NOT correspond to something in an array
# 16 = value must be a valid URI
# 17 = value must NOT exist in an array
# 18 = value must be a valid date. Input $var must be YYYY-MM-DD format
# 19 = value must NOT contain typical mail header text such as "content-type:..." etc.
# 20 = value must NOT be longer than $param
# 21 = value must be a valid IP
# 22 = value must NOT match another value within $param
# 23 = value must NOT already exist in database (free query)
# 24 = make sure that some value (not necessarily $var) returns > 0 results from database query
# 25 = Check reciprocal link
# 26 = Check that spammy words are not in the input
# 27 = Validate that $param isn't in $param2 array
# 28 = Validate that the referer to this form is the actual page doing the processing
# 29 = Validate for valid date
# 30 = Validate that a file was uploaded
# 31 = Validate that $param is in the $param2 array
# 32 = Validate proper SSN format
# 33 = Validate that $var is exactly $param characters in length
# 34 = Validate proper US phone number
# 35 = Validate to make sure image isn't larger than size defined
# 36 = Validate to make sure uploaded file size ($param) isn't greater than $param2 (in KB)
# 37 = Validate string is not longer than 1
# 38 = Validate to ensure $param (intended as product of function call) is TRUE
# 39 = Validate that $var is a valid money format
# 40 = Validate that $var is a valid money value GREATER THAN $param (in dollars)
# 41 = Validate that $var is an array
# 42 = Validate that the value matches an array item (non-strict)
# 43 = Validate against any arbitrary regular expression in the $param variable
# 44 = Validate that the value is between a specified range
# 45 = Validate that the value isn't identical to any other value that has been submitted in the form
# 46 = Validate that a date is between a specified range of start and end dates
# 47 = Validate that $param is FALSE (NOTE: $param intended to be the result of a function call) This is the exact opposite of type 38
# 48 = Validate sameness - tests to make sure $var is identical to $param
# 49 = Validate equivalence between two additional params
# 100 = Wholly generic error, meant to be applied completely ad hoc
################################################################################

    /**
     *
     * validates form elements based on a myriad of different criteria
     *
     * @todo contemplate changing this to make $type optional param, defaulting to what is currently '0'
     *
     * @param    int    $type   the type of validation being performed
     * @param    string $var    the 'name' attribute of the variable being validated
     * @param    string $msg    the message displayed when there's an error
     * @param    mixed  $param  optional parameter passed to assist validation
     * @param    mixed  $param2 optional parameter passed to assist validation
     */
    public function validate($type, $var, $msg, $param = null, $param2 = null)
    {

        // the switch statement lets us select which type of validation we're using
        switch ($type) {

            //////////////////////////////////////////////////////////////////////////
            // Type 0: Validate str > 2
            //////////////////////////////////////////////////////////////////////////
            case '0': // PHP's loose typing makes this a pain in the ass. 
                if (strlen($this->presets[$var]) < 2) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 1: Validate email
            //////////////////////////////////////////////////////////////////////////
            case 1:
                if ((false == gFormUtils::MailVal($this->presets[$var], 2)) || (strlen($this->presets[$var]) < 3)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 2: Validate isset
            // tests to make sure the value exists (with any value)
            //////////////////////////////////////////////////////////////////////////
            case 2:
                if (!isset($this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 3: Validate Dropdown (not "Select One") -
            // do not set for dates or states! use type 6 & 7 for those!
            //////////////////////////////////////////////////////////////////////////
            case 3:
                if ($this->presets[$var] == _("Select One")) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 4: Validate for numbers only
            //////////////////////////////////////////////////////////////////////////
            case 4:
                if (!preg_match("/^([0-9]+)$/", $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 5: Validate for alphanumeric only
            //////////////////////////////////////////////////////////////////////////
            case 5:
                if (!ereg("[A-Za-z0-9]", $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 6: Validate Date Dropdown (not "Month", "Day", or "Year")
            //////////////////////////////////////////////////////////////////////////
            case 6:
                if (($this->presets[$var] == _("Month")) || ($this->presets[$var] == _("Day")) || ($this->presets[$var] == _("Year"))) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 7: Validate State Dropdown (not "Select State")
            //////////////////////////////////////////////////////////////////////////
            case 7:
                if ($this->presets[$var] == _("Select State")) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 8: Validate for a valid money format -
            // "A set of 1 to 3 digits followed by zero or more sets of a comma and three digits."
            //////////////////////////////////////////////////////////////////////////
            case 8:
                if (!ereg("^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(.[0-9]{1,2})?$", $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 9: Validate to ensure value is longer than $param
            //////////////////////////////////////////////////////////////////////////
            case 9:
                if (strlen($this->presets[$var]) < $param) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 10: Validate string should not match $param
            //////////////////////////////////////////////////////////////////////////
            case 10:
                if ($this->presets[$var] == $param) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 11: Validate that neither linebreaks nor returns are located in string
            //////////////////////////////////////////////////////////////////////////
            case 11:
                if (preg_match("/(%0A|%0D|\\n+|\\r+)/i", $this->presets[$var]) != 0) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 12: Validate that the value matches an array item
            //////////////////////////////////////////////////////////////////////////
            case 12:
                if (false == Arrays::inArray($this->presets[$var], $param, true)) {

                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 13: Validate string DOES match $param
            //////////////////////////////////////////////////////////////////////////
            case 13:
                if ($this->presets[$var] !== $param) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 14: Check database to ensure that $var doesn't already
            // exist in the $param field of $param2 table
            // requires the use of gringo db class
            //////////////////////////////////////////////////////////////////////////
            case 14:
                $db = new dbPDO($this->connection['dbName'],
                    $this->connection['user'],
                    $this->connection['pass'],
                    $this->connection['opts'],
                    $this->connection['dbType'],
                    $this->connection['hostORpath'],
                    $this->connection['port']);
                $query = sprintf("SELECT * FROM %s WHERE %s='%s' LIMIT 1", $param2, $param, $this->presets[$var]);
                $data = $db->SelectData($query, PDO::FETCH_ASSOC);
                if (false != $data) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 15: Validate that $var DOES NOT match an array item
            //////////////////////////////////////////////////////////////////////////
            case 15:
                if (in_array($this->presets[$var], $param, true)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 16: Validate URL
            // requires external url_validate function
            //////////////////////////////////////////////////////////////////////////
            case 16:
                if (false == Network::urlValidate($this->presets[$var], false)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 17: Validate that an array item DOES NOT exist within $var
            //////////////////////////////////////////////////////////////////////////
            case 17:
                // $param MUST be an array
                foreach ($param AS $value) {
                    if (stristr($this->presets[$var], $value) !== false) {
                        $this->valfailed[] = $var;
                        $this->valmessage["$var"] = $msg;
                    }
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 18: Validate Date
            //////////////////////////////////////////////////////////////////////////
            case 18:
                list ($varmonth, $varday, $varyear) = explode("-", ${"$var"});
                if (false == checkdate($varmonth, $varday, $varyear)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 19: Validate Against Header Injection
            //////////////////////////////////////////////////////////////////////////
            case 19:
                foreach ($this->config['bad_strings'] as $bad_string) {
                    if (eregi($bad_string, strtolower($this->presets[$var]))) {
                        $this->valfailed[] = $var;
                        $this->valmessage["$var"] = $msg;
                    }
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 20: Validate to make sure string is NOT longer than $param
            //////////////////////////////////////////////////////////////////////////
            case 20:
                if (strlen($this->presets[$var]) > $param) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 21: Validate to make sure value is a valid IP address
            // requires external is_valid_ip function
            //////////////////////////////////////////////////////////////////////////
            case 21:
                if (false == Network::is_valid_ip($this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 22: Super simple email validation
            //////////////////////////////////////////////////////////////////////////
            case 22:
                $item_match = explode("@", $this->presets[$var]);
                if (in_array($item_match[1], $param)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 23: Check database to ensure that item doesn't already exist in
            //	database. Essentially a more flexible version of Type 14
            //	use this if you need to do JOINs, etc.
            //////////////////////////////////////////////////////////////////////////
            case 23:
                $db = new dbPDO($this->connection['dbName'],
                    $this->connection['user'],
                    $this->connection['pass'],
                    $this->connection['opts'],
                    $this->connection['dbType'],
                    $this->connection['hostORpath'],
                    $this->connection['port']);
                $data = $db->SelectData($param);
                if (false != $data) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 24: Check database to ensure that > 0 records are returned for
            // 	a given database query.  This is very different, as this does not
            //	necessarily validate that $var exists, but rather something that
            //	$var depends on - for instance, verifying that a particular category
            //	exists before inserting something into that category
            //////////////////////////////////////////////////////////////////////////
            case 24:
                $db = new dbPDO($this->connection['dbName'],
                    $this->connection['user'],
                    $this->connection['pass'],
                    $this->connection['opts'],
                    $this->connection['dbType'],
                    $this->connection['hostORpath'],
                    $this->connection['port']);
                $data = $db->SelectData($param);
                if (count($data) < 1) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 25: Check reciprocal link
            // requires use of external check_back_link function
            //////////////////////////////////////////////////////////////////////////
            case 25:
                if (false == Network::checkBackLink($this->presets[$var], $param)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 26: Check that spammy words are not in the input
            //////////////////////////////////////////////////////////////////////////
            case 26:
                if (Arrays::inArray($this->presets[$var], $param)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 27: Validate that $param isn't in $param2 array (not necessarily
            //	a way to validate the $var, but something associated with it)
            //////////////////////////////////////////////////////////////////////////
            case 27:
                if (false == Arrays::inArray($param, $param2, true)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 28: Validate that the referer to this form is the actual page doing
            //	the processing. This is not a particularly strong validation
            //  method, but here anyway
            //////////////////////////////////////////////////////////////////////////
            case 28:
                $referer = $_SERVER['HTTP_REFERER'];
                $self_url = sprintf('http%s://%s%s', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == true ? 's' : ''), $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
                if (($referer == "") || ($self_url != $referer)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 29: Validate date if the date is $param (instead of $var, which is
            // the case of type 18) requires external check_date function
            //////////////////////////////////////////////////////////////////////////
            case 29:
                if (false == gFormUtils::check_date($param)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 30: Validate that a file was uploaded
            //////////////////////////////////////////////////////////////////////////
            case 30:
                if (false == is_uploaded_file($param)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 31: Validate that $var DOES NOT match an array item
            //////////////////////////////////////////////////////////////////////////
            case 31:
                if (false == Arrays::inArray($param, $param2, true)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 32: Validate that $var is a valid SSN
            //////////////////////////////////////////////////////////////////////////
            case 32:
                if (!preg_match('/^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d\3(?!0000)\d{4}$/', $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 33: Validate that $var is exactly $param characters in length
            //////////////////////////////////////////////////////////////////////////
            case 33:
                if (!strlen($this->presets[$var]) == $param) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 34: Validate to make sure value is a valid US telephone number
            //////////////////////////////////////////////////////////////////////////
            case 34:
                if (!ereg("/[0-1]{0,1}\(\d{3}\)\s*\d{3}\-\d{4}/", $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 35: Validate to make sure image isn't larger than size defined
            //////////////////////////////////////////////////////////////////////////
            case 35:
                list($width, $height, $type, $attr) = getimagesize($this->presets[$var]);
                if (($width > $param) || ($height > $param2)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 36: Validate to make sure uploaded file isn't larger than (data) size defined
            //////////////////////////////////////////////////////////////////////////
            case 36:
                if ($param > $param2 * 1024) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 37: Validate string is not longer than 1
            //////////////////////////////////////////////////////////////////////////
            case 37:
                if (strlen(trim($this->presets[$var])) > 1) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 38: Validate that $param is TRUE
            // (NOTE: $param intended to be the result of a function call)
            // To validate that $param is FALSE use type 47
            //////////////////////////////////////////////////////////////////////////
            case 38:
                if ($param == false) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 39: Validate that $var is a valid money value
            // unlike type 8, this doesn't require commas. 
            // Use this for the money value in credit card transactions
            //////////////////////////////////////////////////////////////////////////
            case 39:
                if (!preg_match('/^[0-9]*\.?[0-9]+$/', $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 40: Validate that $var is a valid money value GREATER THAN $param
            // Same value as above but looks at the value
            //////////////////////////////////////////////////////////////////////////
            case 40:
                if (!preg_match('/^[0-9]*\.?[0-9]+$/', $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                } else {
                    list($dollars, $cents) = explode(".", $this->presets[$var]);
                    if ($dollars < $param) {
                        $this->valfailed[] = $var;
                        $this->valmessage["$var"] = $msg;
                    }
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 41: Validate that $var is an array
            //////////////////////////////////////////////////////////////////////////
            case 41:
                if (!is_array($this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 42: Validate that the value matches an array item (non-strict)
            // This is needed in case the $param array is an array of integers.  All
            // values from $_POST will be strings
            //////////////////////////////////////////////////////////////////////////
            case 42:
                if (!in_array($this->presets[$var], $param)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            ////////////////////////////////////////////////////////////////////////////////////
            // Type 43: Validate against any arbitrary regular expression in the $param variable
            ////////////////////////////////////////////////////////////////////////////////////
            case 43:
                if (!preg_match($param, $this->presets[$var])) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            ////////////////////////////////////////////////////////////////////////////
            // Type 44: Validate that the value is between a specified range of numbers
            ////////////////////////////////////////////////////////////////////////////
            case 44:
                if (false == Math::isBetween($this->presets[$var], $param, $param2)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////
            // Type 45: Validate that the value isn't identical to any other value 
            // that has been submitted in the form
            /////////////////////////////////////////////////////////////////////
            case 45:
                $_FAILED = false; // initialize
                // only continue if this $var exists in the presets
                if (in_array($this->presets[$var], $this->presets)) {
                    // loop through all the presets
                    foreach ($this->presets AS $key => $val) {
                        // if this preset key is different than the one we're validating, continue
                        if ($key !== $var) {
                            // this means that another key has the exact value
                            if ($key == $this->presets[$var]) {
                                $_FAILED = true;
                            }
                        }
                    }
                    if (false != $_FAILED) {
                        $this->valfailed[] = $var;
                        $this->valmessage["$var"] = $msg;
                    }
                }
                //quick garbage collection. Unset $_FAILED since we don't need it anymore
                // @todo potentially unneeded. Must test.  
                unset($_FAILED);
                break;

            //////////////////////////////////////////////////////////////////////
            // Type 46: Validate that a date is between a specified range of start and end dates
            // :GOTCHA  Uses strtotime. You are responsible for understanding how strtotime works!
            /////////////////////////////////////////////////////////////////////
            case 46:
                if (!gFormUtils::isDateBetween($this->presets[$var], $param, $param2)) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 47: Validate that $param is FALSE
            // (NOTE: $param intended to be the result of a function call)
            // This is the exact opposite of type 38
            //////////////////////////////////////////////////////////////////////////
            case 47:
                if ($param == true) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 48: Validate sameness
            // tests to make sure the value is identical to $param
            //////////////////////////////////////////////////////////////////////////
            case 48:
                if (strtolower(trim($this->presets[$var])) !== strtolower(trim($param))) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;


            //////////////////////////////////////////////////////////////////////////
            // Type 49: Validate equivalence between two additional params
            //////////////////////////////////////////////////////////////////////////
            case 49:
                if (strtolower(trim($param)) !== strtolower(trim($param2))) {
                    $this->valfailed[] = $var;
                    $this->valmessage["$var"] = $msg;
                }
                break;

            //////////////////////////////////////////////////////////////////////////
            // Type 100: Completely generic ad-hoc messaging
            //////////////////////////////////////////////////////////////////////////
            case 100:
                $this->valfailed[] = $var;
                $this->valmessage["$var"] = $msg;

                break;
        }
    }

    /**
     *
     * creates a dropdown menu for picking the day
     * called as a portion of a date dropdown set
     *
     * @param    array $settings        array of settings with the following keys
     *                                  'name' - the select element's 'name' attribute value
     *                                  'id' -  the select element's 'id' attribute value
     *                                  'default_label' - the default label for an empty <option>
     *                                  'label' - the text label for this element
     *                                  'default' - the default value
     *                                  'extras' - anything extra appended to the <select> element, such as javascript
     *
     * @return    string    the $output variable is populated with the HTML
     */
    public function daydrop($settings)
    {
        $output = '';

        // take apart the settings areay
        $name = $settings['name']; // select element's 'name' attribute
        $id = $settings['id']; // select element's 'id' attribute
        $default_label = $settings['default_label']; // default selected item
        $label = $settings['label']; // label
        $default = $this->presets[$name];
        $extras = $settings['extras']; // any extra goodies being appended to the <select> element

        $output .= "<label for=\"$id\"><span>$label</span></label>";
        $output .= "<select name=\"$name\" id=\"$id\"";

        if ($settings['required'] == true) {
            $output .= " aria-required=\"true\" ";
            $output .= " required=\"required\" ";
        }

        $output .= $extras . ">\n";

        if ($default_label) {
            $output .= "<option value=\"\">$default_label</option>\n";
        }

        for ($i = 1; $i <= 31; $i++) {
            if (strlen($i) < 2) {
                $i = "0" . $i;
            }
            $output .= "<option value=\"$i\"";

            if ($i == $default) {
                $output .= " selected=\"selected\"";
            }

            $output .= ">$i</option>\n";
        }
        $output .= "</select>\n";

        return $output;
    }

    /**
     *
     * creates a dropdown menu for picking the month
     * portion of a date dropdown set
     *
     * @param    array $settings        array of settings with the following keys
     *                                  'name' - the select element's 'name' attribute value
     *                                  'id' -  the select element's 'id' attribute value
     *                                  'default_label' - the default label for an empty <option>
     *                                  'label' - the text label for this element
     *                                  'default' - the default value
     *                                  'extras' - anything extra appended to the <select> element, such as javascript
     *
     * @return    string    the $output variable is populated with the HTML
     */
    public function monthdrop($settings)
    {
        $output = '';

        // take apart the settings areay
        $name = $settings['name']; // select element's 'name' attribute
        $id = $settings['id']; // select element's 'id' attribute
        $default_label = $settings['default_label']; // default selected item
        $label = $settings['label']; // label
        $default = $this->presets[$name];
        $extras = $settings['extras']; // any extra goodies being appended to the <select> element

        $output .= "<label for=\"$id\"><span>$label</span></label>";
        $output .= "<select name=\"$name\" id=\"$id\"";

        if ($settings['required'] == true) {
            $output .= " aria-required=\"true\" ";
            $output .= " required=\"required\" ";
        }

        $output .= $extras . ">\n";

        if ($default_label) {
            $output .= "<option value=\"\">$default_label</option>\n";
        }

        for ($i = 1; $i <= 12; $i++) {
            $monthname = date("F", mktime(12, 0, 0, $i, 1, date("Y")));
            if (strlen($i) < 2) {
                $i = "0" . $i;
            }
            $output .= "<option value=\"$i\"";

            if ($i == $default) {
                $output .= " selected=\"selected\"";
            }

            $output .= ">$monthname</option>\n";
        }
        $output .= "</select>\n";

        return $output;
    }

    /**
     *
     * creates a dropdown menu for picking the day
     * portion of a date dropdown set
     *
     * @param    array $settings        array of settings with the following keys
     *                                  'name' - the select element's 'name' attribute value
     *                                  'id' -  the select element's 'id' attribute value
     *                                  'default_label' - the default label for an empty <option>
     *                                  'label' - the text label for this element
     *                                  'default' - the default value
     *                                  'extras' - anything extra appended to the <select> element, such as javascript
     *
     * @return    string    the $output variable is populated with the HTML
     */
    public function yeardrop($settings)
    {
        $output = '';

        // take apart the settings array
        $name = $settings['name']; // select element's 'name' attribute
        $id = $settings['id']; // select element's 'id' attribute
        $default_label = $settings['default_label']; // default selected item
        $label = $settings['label']; // label
        $default = $this->presets[$name]; //default value
        $start = $settings['start'];
        $end = $settings['end'];
        $extras = $settings['extras']; // any extra goodies being appended to the <select> element
        // check that the start date is valid
        // default to 1970 if the year is invalid
        if ((!$start) || (false == gFormUtils::validyear($start))) {
            $start = "1970";
        }

        // check that the end date is valid
        // default to this year if one wasn't set
        if (false == gFormUtils::validyear($end)) {
            $end = date("Y");
        }

        $output .= "<label for=\"$id\"><span>$label</span></label>";
        $output .= "<select name=\"$name\" id=\"$id\"";

        if ($settings['required'] == true) {
            $output .= " aria-required=\"true\" ";
            $output .= " required=\"required\" ";
        }

        $output .= $extras . ">\n";

        if ($default_label) {
            $output .= "<option value=\"\">$default_label</option>\n";
        }

        // make an array of the options
        $years = range($start, $end);

        foreach ($years AS $opt) {

            $output .= "<option value=\"$opt\"";

            if ($opt == $default) {
                $output .= " selected=\"selected\"";
            }

            $output .= ">$opt</option>\n";
        }
        $output .= "</select>\n";

        return $output;
    }

    /**
     *
     * @param   string $s
     */
    protected function addToDisplay($s)
    {
        $this->_output .= $s;
    }

    /**
     *
     * prints the form to the page
     */
    public function display()
    {
        echo $this->_output;
    }


    /**
     * instead of printing the form to the page
     * this returns the form's output as a string
     */
    public function returnOutput()
    {
        return $this->_output;
    }
}
