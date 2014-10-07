<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Symfony\Component\Intl\ResourceBundle\Reader\BinaryBundleReader;
use Symfony\Component\Intl\ResourceBundle\Reader\StructuredBundleReader;

/**
 * Helper class above Symfony Intl & Icu.
 * Should not be necessary because Intl::getRegionBundle()->getCountryNames()
 * but generate a fatal error ResourceBundle::get(): Cannot load resource element 'Countries'
 */
class IntlHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [
        'locale'//string
    ];

    protected $countries = [];
    
    public function __construct(array $mandatories = [], array $options = [])
    {
        parent::__construct($mandatories, $options);
        
        $reader = new StructuredBundleReader(new BinaryBundleReader());
        $countries = $reader->read(__DIR__ . '/../../../vendor/symfony/icu/Symfony/Component/Icu/Resources/data/region', $this->mandatories['locale'])->getIterator()->get("Countries");

        foreach ($countries as $code => $name) {
            if (!ctype_digit((string) $code)) {
                $this->countries[$code] = $name;
            }
        }
        unset($this->countries['ZZ']);
    }

    public function getCountryNames()
    {
        return $this->countries;
    }
    
    public function getCountryName($code)
    {
        return $this->countries[$code];
    }
}
