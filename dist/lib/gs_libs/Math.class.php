<?php


/**
 * Class Math
 */
class Math
{

    /**
     * determines if a variable is a whole number
     *
     * @param   mixed $var what we're testing
     *
     * @return  bool
     */
    public static function isWholeNum($var)
    {
        return preg_match('@^[-]?[0-9]+$@', $var) === 1;
    }

    /**
     * takes two numbers - one large (the total) and one small (the percentage)
     * and returns the amount representing that percentage.
     * In other words: Given 20% of '5', would return '1'.
     *
     * @param $num_amount
     * @param $num_total
     *
     * @return    int
     */
    public static function makePercentage($num_amount, $num_total)
    {
        if (($num_amount == 0) || ($num_total == 0)) {
            return 0;
        } else {
            $count = ($num_total / 100) * $num_amount;
            $count = number_format($count, 2, '.', ',');

            return $count;
        }
    }


    /**
     * @param $max
     * @param $min
     *
     * @return float
     */
    public static function pctDifference($max, $min)
    {
        return Math::zeroSafeDivide((($max - $min) / $max)) * 100;
    }


    /**
     * @param $total
     * @param $deduction
     *
     * @return float
     */
    public static function deductPct($total, $deduction)
    {
        return round(Math::zeroSafeDivide($total * ((100 - $deduction) / 100)), 2);
    }


    /**
     * takes two numbers - one large (the total) and one  small (the part) and gets a percentage
     *
     * @param    int $total
     * @param    int $number
     * @param   int  $precision
     *
     * @return    int
     */
    public static function getPercentage($total, $number, $precision = 2)
    {
        return round((($number / $total) * 100), $precision);
    }

    /**
     * This function will let you round to an arbitrary non-zero
     * number.  Zero of course causes a division by zero.
     *
     * @param float $number the number being rounded
     * @param int   $to     the number to round to
     *
     * @return int
     */
    public static function roundTo($number, $to)
    {
        return round($number / $to, 0) * $to;
    }

    /**
     * calculates a sum based on all of the parameters fed to it
     *
     * @return    int
     */
    public static function calcSum()
    {
        $args = func_num_args();

        if ($args == 0) {
            return 0;
        } else {
            $sum = 0;
            for ($i = 0; $i < $args; $i++) {
                $sum += func_get_arg($i);
            }

            return $sum;
        }
    }

    /**
     * calculates an average based on all of the parameters fed to it
     *
     * @return    int
     */
    public static function calcAvg()
    {
        $args = func_num_args();

        if ($args == 0) {
            return 0;
        } else {
            $sum = 0;
            for ($i = 0; $i < $args; $i++) {
                $sum += func_get_arg($i);
            }

            return $sum / $args;
        }
    }

    /**
     *
     * determines if a number is between a range of numbers
     *
     * @param   int $x    the number to check
     * @param   int $low  the low number of the range
     * @param   int $high the high number of the range
     *
     * @return  bool
     */
    public static function isBetween($x, $low, $high)
    {
        return (($x >= $low) && ($x <= $high));
    }

    /**
     * avoids PHP errors when one operand in a division operation is 0
     *
     * @param   int|float $leftOperand  the left operand
     * @param   int|float $rightOperand the right operand
     * @param   mixed     $errVal       default return value (returned on eror)
     *
     * @return  float
     */
    public static function zeroSafeDivide($leftOperand = 0, $rightOperand = 0, $errVal = false)
    {
        if ((!is_numeric($leftOperand)) || (!is_numeric($rightOperand))) {
            return $errVal;
        }
        if (($leftOperand == 0) || ($rightOperand == 0)) {
            return $errVal;
        }

        return ($leftOperand / $rightOperand);
    }

    /**
     * @param $num
     *
     * @return bool
     */
    public static function isEven($num)
    {
        if ($num & 1) {
            return false;
        }

        return true;
    }

    /**
     * @param $num
     *
     * @return bool
     */
    public static function isOdd($num)
    {
        if ($num & 1) {
            return true;
        }

        return false;
    }

    /**
     * @param     $bytes
     * @param int $precision
     *
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
