<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;

/**
 * Helper class to manipulate php configuration.
 *
 */
class PhpHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    protected $oldValues = [];

    /**
     * Changes PHP configuration
     *
     * @param string $varname
     * @param mixed  $newValue
     *
     * @return void | Exception
     */
    public function setConfiguration($varname, $newValue)
    {
        //http://www.php.net/manual/en/configuration.changes.modes.php
        $oldValue = ini_get($varname);
        if (is_bool($newValue)) {
            $newValue = (($newValue) ? '1' : '0');
        }
        if ((false !== $oldValue) && ($newValue != $oldValue)) {
            $this->oldValues[$varname] = $oldValue;
            ini_set($varname, $newValue);
            if (ini_get($varname) !== $newValue) {
                throw new \Exception(sprintf("Value of %s cannot be changed into %s()", $varname, __FUNCTION__));
            }
        }
    }
    
    /**
     * Resets PHP configuration
     *
     * @param string $varname
     *
     * @return void
     */
    public function resetConfiguration($varname)
    {
        if (array_key_exists($varname, $this->oldValues)) {
            ini_set($varname, $this->oldValues[$varname]);
        }
    }
}
