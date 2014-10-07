<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;

/**
 * Helper class to manipulate numbers.
 *
 */
class NumberHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    /**
     * Format a number using diffrent default values than regular function
     * See http://fr2.php.net/manual/fr/function.number-format.php
     *
     * @param float|string    $number
     * @param integer         $decimals
     * @param string          $decPoint
     * @param string          $thousandsSep
     *     
     * @return string
     */
    public function formatNumber($number, $decimals = 2, $decPoint = '.', $thousandsSep = '')
    {
        if (is_null($number) || empty($number)) {
            return $number;
        }
        if (is_string($number)) {
            $number = floatval($number);
        }
        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }
}
