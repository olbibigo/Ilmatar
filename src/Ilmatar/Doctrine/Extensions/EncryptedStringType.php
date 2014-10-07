<?php
namespace Ilmatar\Doctrine\Extensions;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Ilmatar\HelperFactory;

/**
 * My custom datatype.
 */
class EncryptedStringType extends Type
{
    const ENCRYPTED_STRING = 'encryptedstring';
    const KEY              = 'Rijndael-128';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : HelperFactory::build('SecurityHelper')->decryptString($value, self::KEY);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value === null) ? null : HelperFactory::build('SecurityHelper')->encryptString($value, self::KEY);
    }

    public function getName()
    {
        return self::ENCRYPTED_STRING;
    }
}
