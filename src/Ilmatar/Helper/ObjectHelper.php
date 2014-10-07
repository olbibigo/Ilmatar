<?php
namespace Ilmatar\Helper;

use \Ilmatar\BaseHelper;
use \Ilmatar\HelperFactory;
use Symfony\Component\Translation\Translator;

/**
 * Helper class to manipulate object.
 *
 */
class ObjectHelper extends BaseHelper
{
    const ASSOCIATION_SUFFIX = '_id';
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    protected $arrayHelper;
    protected $cacheMethod   = [];
    protected $cacheProperty = [];
    
    public function __construct(array $mandatories = [], array $options = [])
    {
        parent::__construct($mandatories, $options);
       
        $this->arrayHelper = HelperFactory::build(
            'ArrayHelper',
            [/*Nothing right now*/]
        );
    }
    /**
     * Transforms an object into an XML string or document
     * See ArrayHelper::getXmlFrom[]
     *
     */
    public function getXmlFromObjects(array $objects, array $fields, $rootNodeName, $itemNodeName, $returnType = 'string', $isStrippedTag = true)
    {
        $array = $this->transform($objects, $fields);
        return $this->arrayHelper->getXmlFromArray($array, $rootNodeName, $itemNodeName, $returnType, $isStrippedTag);
    }
    /**
     * Transforms an array into an Json string
     * See ArrayHelper::getJsonFrom[]
     */
    public function getJsonFromObjects(array $objects, array $fields)
    {
        $array = $this->transform($objects, $fields);
        return $this->arrayHelper->getJsonFromArray($array);
    }
    /**
     * Transforms an array into an CSV string
     * See ArrayHelper::getCsvFrom[]
     */
    public function getCsvFromObjects(array $objects, array $fields, $delimiter = ';', Translator $translator = null, $isStrippedTag = true)
    {
        $array = $this->transform($objects, $fields);
        return $this->arrayHelper->getCsvFromArray($array, $delimiter, $translator, $isStrippedTag);
    }

    /**
     * Transforms an array into a HTML select
     * See ArrayHelper::getHTMLSelectFrom[]
     */
    public function getHTMLSelectFromObjects(array $objects, array $fields, $keyName, $valueName, $isWithEmpty = false, array $attributes = [])
    {
        $array = $this->transform($objects, $fields);
        if ($isWithEmpty) {
            array_unshift($array, [$keyName => "", $valueName => "---"]);
        }
        return $this->arrayHelper->getHTMLSelectFromArray($array, $keyName, $valueName, $attributes);
    }
    
    /**
     * Checks if a method can be called (exists and public)
     * If magic caller __call() exists, will return true
     *
     * @param  mixed   $object  Object or class name
     * @param  string  $method  Method name to check
     * @return boolean
     */
    public function isValidMethod($object, $method)
    {
        $class =  is_string($object) ? $object : get_class($object);
    
        if (!isset($this->cacheMethod[$class])) {
            $this->cacheMethod[$class] = [];
        }
        if (!isset($this->cacheMethod[$class][$method])) {
            $this->cacheMethod[$class][$method] =
                in_array($method, get_class_methods($object), true) || in_array('__call', get_class_methods($object), true);
        }
        return $this->cacheMethod[$class][$method];
    }
    
    /**
     * Checks if a property can be called (exists and public)
     * If magic getter __get() exists, will return true
     *
     * @param  mixed   $object   Object or class name
     * @param  string  $property Property name to check
     * @return boolean
     */
    public function isValidProperty($object, $property)
    {
        $class =  is_string($object) ? $object : get_class($object);
        
        if (!isset($this->cacheProperty[$class])) {
            $this->cacheProperty[$class] = [];
        }
        if (!isset($this->cacheProperty[$class][$property])) {
            $this->cacheProperty[$class][$property] =
                array_key_exists($property, get_object_vars($object)) || in_array('__get', get_class_methods($object), true);
        }
        return $this->cacheProperty[$class][$property];
    }
    
    protected function transform($objects, $fields)
    {
        $stringHelper = HelperFactory::build('StringHelper');
        $result = [];
        foreach ($objects as $object) {
            $obj = [];
            foreach ($fields as $field) {
                //use getter first if available then attributes if available
                $method = 'get' . $stringHelper->snakeToCamel($field);
                if ($this->isValidMethod($object, $method)) {
                    $val = $object->$method();
                } elseif ($this->isValidProperty($object, $field)) {
                    $val = $object->$field;
                }
                if (is_object($val)
                  && (false !== stripos(get_class($val), 'Entities'))) {
                    $obj[$field . self::ASSOCIATION_SUFFIX] = $val->getId();
                } else {
                    $obj[$field] = $val;
                }
            }
            $result[] = $obj;
        }
        return $result;
    }
}
