<?php

/**
 *
 * Class for the creation of simple data tables
 *
 */
class simpleTable
{

    /**
     * @param string $default_cell_emptychars
     * @param bool   $alternate
     * @param string $altclass
     */
    public function __construct($default_cell_emptychars = '', $alternate = true, $altclass = 'odd_row')
    {
        $this->default_emptychars = $default_cell_emptychars;
        $this->alternate = $alternate;
        $this->altclass = $altclass;
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
     *    CORE ATTRIBUTES - this section is just reference info for use with the class
     *    ATTRIBUTE    VALUE TYPE        DESCRIPTION
     *    id          ID                -- document-wide unique id --
     *    class      CDATA            -- space-separated list of classes --
     *    style      %StyleSheet;        -- associated style info --
     *    title      %Text;            -- advisory title --
     *
     *    I18N (Internationalization)
     *    ATTRIBUTE    VALUE TYPE        DESCRIPTION
     *    lang        %LanguageCode;    -- ISO language code --
     *    dir         (ltr|rtl)        -- direction for weak/neutral text --"
     *
     *    EVENT HANDLERS
     *    ATTRIBUTE    VALUE TYPE        DESCRIPTION
     *    onclick     %Script;         -- a pointer button was clicked --
     *    ondblclick  %Script;         -- a pointer button was double clicked--
     *    onmousedown %Script;         -- a pointer button was pressed down --
     *    onmouseup   %Script;         -- a pointer button was released --
     *    onmouseover %Script;         -- a pointer was moved onto --
     *    onmousemove %Script;         -- a pointer was moved within --
     *    onmouseout  %Script;         -- a pointer was moved away --
     *    onkeypress  %Script;         -- a key was pressed and released --
     *    onkeydown   %Script;         -- a key was pressed down --
     *    onkeyup     %Script;         -- a key was released --"
     */

    /**
     *
     * returns a string of attributes
     *
     * @param    mixed $attrs attributes, most often an array, can also be a string
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function loop_attributes($attrs = "")
    {
        $output = '';

        if ($attrs !== '') {
            // if the attributes aren't an array, treat them like a string
            if (!is_array($attrs)) {
                $output .= $attrs;
            } // otherwise, loop through them like an array
            else {
                foreach ($attrs AS $key => $val) {
                    $output .= " $key=\"$val\" ";
                }
            }
        }

        return $output;
    }

    /**
     *
     * begins an HTML table
     *
     * @param    array  $attrs     array of attributes for <table>
     * @param    string $caption   text for the <caption> element
     * @param    array  $cap_attrs array of attributes for <caption>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function tbl_open($attrs = null, $caption = null, $cap_attrs = null)
    {
        $output = '';

        // Open the element
        $output .= "<table";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <table> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *      summary     %Text;           -- purpose/structure for speech output--
         *      width       %Length;         -- table width --
         *      border      %Pixels;         -- controls frame width around table --
         *      frame       %TFrame;         -- which parts of frame to render --
         *      rules       %TRules;         -- rulings between rows and cols --
         *      cellspacing %Length;         -- spacing between cells --
         *      cellpadding %Length;         -- spacing within cells --
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */

        $output .= $this->loop_attributes($attrs);

        // close the <table> element
        $output .= ">";

        if ($caption !== null) {
            // <caption> is able to use any of the valid CORE and I18N Attributes
            $output .= "<caption";

            $output .= $this->loop_attributes($cap_attrs);
            //close the caption
            $output .= ">" . $caption . "</caption>";
        }

        return $output;
    }

    /**
     *
     * generates an HTML <colgroup> element and its contents
     *
     * @param    string $data  the data to be entered between the opening and closing <colgroup> elements
     * @param    array  $attrs array of attributes for <colgroup>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function colgroup($data = '', $attrs = null)
    {
        $output = '';

        $output .= "<colgroup";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <colgroup> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *    span         NUMBER         1 -- default number of columns in group --
         *    width        %MultiLength;    -- default width for enclosed COLs --
         *    halign                        -- horizontal alignment in cells --
         *    valign                        -- vertical alignment in cells --
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */

        $output .= $this->loop_attributes($attrs);

        $output .= ">";

        // If $data is not an array, output it as a straight string
        if (!is_array($data)) {
            $output .= $data;
        }
        // now, loop through the attributes and print them
        foreach ($data AS $val) {
            $output .= $val;
        }

        $output .= "</colgroup>";

        return $output;
    }

    /**
     *
     * generates an HTML <col> element and its attributes
     *
     * @param    array $attrs array of attributes for <col>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function col($attrs = null)
    {
        $output = '';

        $output .= "<col ";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <col> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *      span        NUMBER         1 -- COL attributes affect N columns --
         *      width       %MultiLength;    -- column width specification --
         *      halign                       -- horizontal alignment in cells --
         *      valign                       -- vertical alignment in cells --
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */

        $output .= $this->loop_attributes($attrs);

        $output .= ">";

        return $output;
    }

    /**
     *
     * generates an HTML <thead> element and its contents
     *
     * @param    mixed $data  the data to be inserted into the <thead>
     * @param    array $attrs array of attributes for <thead>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function tbl_head($data, $attrs = null)
    {
        $output = '';

        $output .= "<thead";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <thead> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */

        $output .= $this->loop_attributes($attrs);

        $output .= ">";

        // If $data is not an array, output it as a straight string
        if (!is_array($data)) {
            $output .= $data;
        } else {
            // now, loop through the attributes and print them
            foreach ($data AS $val) {
                $output .= $val;
            }
        }

        $output .= "</thead>";

        return $output;
    }

    /**
     *
     * generates the HTML for the table header row
     *
     * @param    array $headrowdata array of data for the headers
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function assemble_headers($headrowdata)
    {
        $output = '';
        $thead_data = '';

        // assemble the TH elements
        foreach ($headrowdata AS $thdata) {
            $cell_attrs['scope'] = "col";
            $thead_data .= $this->tbl_cell($thdata, "TH", $cell_attrs);
            // create string of cell data
        }

        // assemble the table head
        $output = $this->tbl_head($this->tbl_row($thead_data));

        // put the row in a <thead>

        return $output;
    }

    /**
     *
     * generates an HTML <tr> element and its contents
     *
     * @param    mixed $data  the data to be inserted into the <tr>
     * @param    array $attrs array of attributes for <table>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function tbl_row($data, $attrs = null)
    {
        $output = '';

        $output .= "<tr ";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <thead> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */

        $output .= $this->loop_attributes($attrs);

        $output .= ">";

        // If $data is not an array, output it as a straight string
        if (!is_array($data)) {
            $output .= $data;
        } else {
            // now, loop through the attributes and print them
            foreach ($data AS $val) {
                $output .= $val;
            }
        }
        // end function tbl_row()

        $output .= "</tr>";

        return $output;
    }

    /**
     *
     * generates an HTML <th> or <td> element and its contents
     *
     * @param    mixed  $data  the data to be inserted into the cell
     * @param    string $type  whether this is a <td> or <th> cell
     * @param    array  $attrs array of attributes for <table>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function tbl_cell($data, $type = "TD", $attrs = null)
    {
        $output = '';

        if ($type == "TH") {
            $output .= "<th ";
        } else {
            $output .= "<td ";
        }

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <th> or <td> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *    abbr        %Text;     -- abbreviation for header cell --
         *    axis        CDATA      -- comma-separated list of related headers--
         *    headers     IDREFS     -- list of id's for header cells --
         *    scope       %Scope;    -- scope covered by header cells --
         *    rowspan     NUMBER   1 -- number of rows spanned by cell --
         *    colspan     NUMBER   1 -- number of cols spanned by cell --
         *    halign                 -- horizontal alignment in cells --
         *    valign                 -- vertical alignment in cells --
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */

        $output .= $this->loop_attributes($attrs);

        $output .= ">" . $data . "</td>";

        return $output;
    }

    /**
     *
     * generates an HTML <tbody> element and its data
     *
     * @param    mixed $data  the data to be inserted into the <tbody>
     * @param    array $attrs array of attributes for <table>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function tbl_body($data, $attrs = null)
    {
        $output = '';

        $output .= "<tbody ";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <tbody> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *  halign    -- horizontal alignment in cells --
         *  valign    -- vertical alignment in cells --
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */


        $output .= $this->loop_attributes($attrs);

        $output .= ">";

        // If $data is not an array, output it as a straight string
        if (!is_array($data)) {
            $output .= $data;
        } else {
            // now, loop through the attributes and print them
            foreach ($data AS $val) {
                $output .= $val;
            }
        }

        $output .= "</tbody>";

        return $output;
    }

    /**
     *
     * generates an HTML <tfoot> element and its contents
     *
     * @param    mixed $data  the data to be inserted into the <tfoot>
     * @param    array $attrs array of attributes for <table>
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function tbl_foot($data, $attrs = null)
    {
        $output = '';

        $output .= "<tfoot ";

        /**
         *    $attrs is an array of any of the possible
         *    attributes valid in the HTML spec for the
         *    <tbody> element.  I name them here just for
         *    reference:
         *      (In addition to the attributes listed below, this element can use any of the attributes
         *        listed at the head of this document)
         *  halign -- horizontal alignment in cells --
         *  valign -- vertical alignment in cells --
         *    These attributes must be arranged like:
         *    $attrs['attribute'] = "value";
         */


        $output .= $this->loop_attributes($attrs);

        $output .= ">";

        // If $data is not an array, output it as a straight string
        if (!is_array($data)) {
            $output .= $data;
        }
        // now, loop through the attributes and print them
        foreach ($data AS $val) {
            $output .= $val;
        }

        $output .= "</tfoot>";

        return $output;
    }

    /**
     *
     * loops throu an array of data assembling each row
     *
     * @param   $data
     *
     * @return    string    the $output variable is populated with the generated HTML
     */
    public function assemble_rows($data)
    {
        // loop through the data, assembling each rows
        $i = 0;

        foreach ($data AS $row) {
            if ($this->alternate == true) {
                // Quick snippet for alternating row colors
                if ($i % 2 == 0) {
                    // do nothing interesting
                    $i++;
                } else {
                    $cell_attrs['class'] = $this->altclass;
                    $i++;
                }
            }

            $x = 0;

            // Loop through each
            foreach ($row AS $val) {
                // Quick snippet for adding row headers
                if ($x == 0) {
                    $cell_attrs['scope'] = "row";
                    $x++;
                }
                $celldata .= $this->tbl_cell($val, "TD", $cell_attrs);
                unset($cell_attrs['scope']);
                // unset the scope
            }

            $tablerows .= $this->tbl_row($celldata);
            unset($cell_attrs);
            //unset the cell_attrs to start over
            unset($celldata);
            // unset the cell data so we can start over
        }

        return $tablerows;
    }

    /**
     *
     * closes the HTML </table> element
     *
     * @return        string    the $output variable is populated with the generated HTML
     */
    public function tbl_close()
    {
        $output = '';

        $output .= "</table>";

        return $output;
    }

    /**
     *
     * @param   string $table_data
     *
     * @return  string
     */
    public function display($table_data)
    {
        return $table_data;
    }

}
