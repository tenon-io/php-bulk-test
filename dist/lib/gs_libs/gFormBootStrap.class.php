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
class gFormBootStrap extends gForm
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
        parent::__construct($action, $method, $connection);
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
     * @param   string $divClass
     */
    public function editbox($label, $varname, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '', $divClass = null)
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

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span></label>\n";

        if (!is_null($divClass)) {
            $output .= "<div class=\"$divClass\">\n";
        }

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
            $output .= "value=\"" . stripslashes(htmlentities($this->presets[$varname])) . "\"";
        }

        $output .= " />\n";

        if (!is_null($divClass)) {
            $output .= "</div>";
        }

        $output .= $postString;

        $output .= "</div>\n";

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
     * @param   string $divClass
     */
    public function dropdown($label, $varname, $value_array, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '', $divClass = null)
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

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span></label>\n";

        if (!is_null($divClass)) {
            $output .= "<div class=\"$divClass\">\n";
        }

        $output .= "<select name=\"$varname\" " . $this->loopAttrs($inputAttrs) . ">\n";

        foreach ($value_array as $item) {
            if ($item == $this->presets[$varname])
                $selected = " selected=\"selected\" ";
            else
                $selected = "";
            $output .= "<option value=\"$item\"" . $selected . ">$item</option>\n";
        }

        $output .= "</select>\n";

        if (!is_null($divClass)) {
            $output .= "</div>";
        }

        $output .= $postString;
        $output .= "</div>\n";

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
     * @param   string $divClass
     */
    public function dropdown_val($label, $varname, $value_array, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '', $divClass = null)
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

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span></label>\n";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        if (!is_null($divClass)) {
            $output .= "<div class=\"$divClass\">\n";
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

        if (!is_null($divClass)) {
            $output .= "</div>";
        }

        $output .= $postString;

        $output .= "</div>\n";

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
     * @param   string $divClass
     */
    public function stateDropdown($label, $varname, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $us_only = false, $postString = '', $divClass = null)
    {
        $output = '';

        //make an ID for this element
        $inputAttrs = $this->setElementID($varname, $inputAttrs);

        $us_only = (strlen($us_only) > 1) ? $us_only : false;

        /////////////////////////////////////////////////////////////////////
        // Prepend the validation error message to this section of the form
        if ((is_array($this->valfailed)) && (in_array($varname, $this->valfailed))) {
            $output .= $this->valError($this->valmessage[$varname], $varname);

            $this->setAttr('aria-describedby', "error" . Strings::stripNonAlphanumeric(trim($varname)), $inputAttrs, 'prepend');

            $this->appendClassAttr($this->config['element_error_class'], $inputAttrs);
        }
        /////////////////////////////////////////////////////////////////////
        $output .= "<div " . $this->loopAttrs($wrapperAttrs) . ">\n";

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span></label>\n";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        if (!is_null($divClass)) {
            $output .= "<div class=\"$divClass\">\n";
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

        if (!is_null($divClass)) {
            $output .= "</div>";
        }

        $output .= $postString;
        $output .= "</div>\n";

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
     * @param   string $divClass
     */
    public function multiSelect($label, $varname, $value_array, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '', $divClass = null)
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

        $output .= "<label for=\"" . $inputAttrs['id'] . "\" " . $this->loopAttrs($labelAttrs) . "><span>$label</span></label>\n";

        if (!is_null($divClass)) {
            $output .= "<div class=\"$divClass\">\n";
        }

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

        if (!is_null($divClass)) {
            $output .= "</div>";
        }

        $output .= $postString;

        $output .= "</div>\n";

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
     * @param   string $divClass
     */
    public function textarea($label, $varname, $required = false, $inputAttrs = null, $wrapperAttrs = null, $labelAttrs = null, $postString = '', $divClass = null)
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

        $output .= "<label for=\"" . $inputAttrs['id'] . "\"" . $this->loopAttrs($labelAttrs) . "><span>$label</span></label>\n";

        if (!is_null($divClass)) {
            $output .= "<div class=\"$divClass\">\n";
        }

        $output .= "<textarea name=\"$varname\" ";

        if ($required == true) {
            $this->setRequiredAttr($inputAttrs);
        }

        $output .= "" . $this->loopAttrs($inputAttrs) . ">" . stripslashes(htmlentities($this->presets[$varname])) . "</textarea>\n";

        if (!is_null($divClass)) {
            $output .= "</div>";
        }

        $output .= $postString;

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

}