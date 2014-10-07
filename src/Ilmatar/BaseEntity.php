<?php
namespace Ilmatar;

use Ilmatar\HelperFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class BaseEntity
{
    public static $allowedHttpMethodForWebservice = [];
    public static $allowedFieldsToImport          = [];

    public function __construct(array $values = [])
    {
        if (!empty($values)) {
            $this->fill($values);
        }
    }

    public function fill($values)
    {
        $stringHelper = HelperFactory::build(
            'StringHelper',
            [],
            ['seedFile' => '']//Not in use
        );

        foreach ($values as $key => $value) {
            $accessor = 'set' . $stringHelper->snakeToCamel($key);
            if (HelperFactory::build('ObjectHelper')->isValidMethod(get_called_class(), $accessor)) {
                $this->$accessor($value);
            }
        }
    }
   
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        //Empty
    }
}
