<?php
namespace Ilmatar;

class HelperFactory
{
    public static function build($helperClass, $mandatories = [], $options = [])
    {
        $namespaces = [
           "\\Project\\Helper\\",
           "\\Ilmatar\\Helper\\",
        ];
        
        foreach ($namespaces as $namespace) {
            $className = $namespace . $helperClass;
            if (class_exists($className)) {
                return new $className($mandatories, $options);
            }
        }
        throw new \Exception('Unknown class ' . $className);
    }
}
