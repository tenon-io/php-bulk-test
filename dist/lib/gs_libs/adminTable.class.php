<?php

/**
 *
 * Class used to create the "Admin" tables in a CRUD system
 *
 */
class adminTable
{

    public $tableContent;

    /**
     *
     */
    public function __construct()
    {
        $this->tableContent = '';
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
     * @param string $string
     */
    public function extContent($string)
    {
        $this->tableContent .= $string;
    }

    /**
     *
     * @param array $array
     *
     * @return string
     */
    public static function loopAttrs($array = null)
    {
        $output = '';

        if (is_null($array)) {
            return '';
        }

        if (!is_array($array)) {
            return $array;
        }

        foreach ($array AS $key => $val) {
            $output .= " $key=\"$val\" ";
        }

        return $output;
    }

    /**
     *
     * @param array  $attrs
     * @param string $caption
     */
    public function open($attrs = null, $caption = null)
    {
        $this->tableContent .= "<table " . adminTable::loopAttrs($attrs) . " >";

        if (!is_null($caption)) {
            $this->tableContent .= '<caption>' . _($caption) . '</caption>';
        }
    }

    /**
     *
     */
    public function close()
    {
        $this->tableContent .= "</table>";
    }

    /**
     *
     * @param array $attrs
     */
    public function startRow($attrs = null)
    {
        $this->tableContent .= "<tr " . adminTable::loopAttrs($attrs) . " >";
    }

    /**
     *
     */
    public function endRow()
    {
        $this->tableContent .= "</tr>";
    }

    /**
     *
     * @param string $data
     * @param array  $attrs
     */
    public function addCell($data, $attrs = null)
    {
        $this->tableContent .= "<td " . adminTable::loopAttrs($attrs) . ">";
        $this->tableContent .= $data;
        $this->tableContent .= "</td>";
    }

    /**
     *
     * @param   string $data
     * @param   string $scope
     * @param   array  $attrs
     */
    public function addHeader($data, $scope, $attrs = null)
    {
        $this->tableContent .= "<th scope=\"$scope\" " . adminTable::loopAttrs($attrs) . ">";
        $this->tableContent .= $data;
        $this->tableContent .= "</th>";
    }

    /**
     *
     * @return string
     */
    public function getAdminTable()
    {
        return $this->tableContent;
    }

}