<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Util\StringUtils;

/**
 * Helper class to manipulate strings.
 *
 */
class StringHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    /**
     * Takes a string_like_this and return a StringLikeThis
     *
     * @param string   $str                    Snake case string
     * @param boolean  $isFirstCharLowerCase   (default = false)
     * @return string                          Camel case string
     */
    public function snakeToCamel($str, $isFirstCharLowerCase = false)
    {
        $return = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($str))));
        return $isFirstCharLowerCase ? lcfirst($return) : $return;
    }

    /**
     * Takes a StringLikeThis and return a string_like_this
     *
     * @param string  $str   Camel case string
     *
     * @return string        Camel snake case string
     */
    public function camelToSnake($str)
    {
        $return = preg_replace_callback(
            '/[A-Z]/',
            function ($matches) {
                return "_" . strtolower($matches[0]);
            },
            $str
        );
        return '_' == $return[0] ? substr($return, 1) : $return;
    }
    
    /**
     * remove all accents from a string
     * 
     * @param string $string
     * @return string
     */
    public function removeAccents($str)
    {
        $string = str_replace(
            ['œ', 'æ', 'Æ'],
            ['oe', 'ae', 'Ae'],
            $str
        );
        mb_regex_encoding('UTF-8');
        $string = mb_ereg_replace('[ÀÁÂÃÄÅĀĂǍẠẢẤẦẨẪẬẮẰẲẴẶǺĄ]', 'A', $string);
        $string = mb_ereg_replace('[àáâãäåāăǎạảấầẩẫậắằẳẵặǻą]', 'a', $string);
        $string = mb_ereg_replace('[ÇĆĈĊČ]', 'C', $string);
        $string = mb_ereg_replace('[çćĉċč]', 'c', $string);
        $string = mb_ereg_replace('[ÐĎĐ]', 'D', $string);
        $string = mb_ereg_replace('[ďđ]', 'd', $string);
        $string = mb_ereg_replace('[ÈÉÊËĒĔĖĘĚẸẺẼẾỀỂỄỆ]', 'E', $string);
        $string = mb_ereg_replace('[èéêëēĕėęěẹẻẽếềểễệ]', 'e', $string);
        $string = mb_ereg_replace('[ĜĞĠĢ]', 'G', $string);
        $string = mb_ereg_replace('[ĝğġģ]', 'g', $string);
        $string = mb_ereg_replace('[ĤĦ]', 'H', $string);
        $string = mb_ereg_replace('[ĥħ]', 'h', $string);
        $string = mb_ereg_replace('[ÌÍÎÏĨĪĬĮİǏỈỊ]', 'I', $string);
        $string = mb_ereg_replace('[ìíîïĩīĭįıǐỉị]', 'i', $string);
        $string = str_replace('Ĵ', 'J', $string);
        $string = str_replace('ĵ', 'j', $string);
        $string = str_replace('Ķ', 'K', $string);
        $string = str_replace('ķ', 'k', $string);
        $string = mb_ereg_replace('[ĹĻĽĿŁ]', 'L', $string);
        $string = mb_ereg_replace('[ĺļľŀł]', 'l', $string);
        $string = mb_ereg_replace('[ÑŃŅŇ]', 'N', $string);
        $string = mb_ereg_replace('[ñńņňŉ]', 'n', $string);
        $string = mb_ereg_replace('[ÒÓÔÕÖØŌŎŐƠǑǾỌỎỐỒỔỖỘỚỜỞỠỢ]', 'O', $string);
        $string = mb_ereg_replace('[òóôõöøōŏőơǒǿọỏốồổỗộớờởỡợð]', 'o', $string);
        $string = mb_ereg_replace('[ŔŖŘ]', 'R', $string);
        $string = mb_ereg_replace('[ŕŗř]', 'r', $string);
        $string = mb_ereg_replace('[ŚŜŞŠ]', 'S', $string);
        $string = mb_ereg_replace('[śŝşš]', 's', $string);
        $string = mb_ereg_replace('[ŢŤŦ]', 'T', $string);
        $string = mb_ereg_replace('[ţťŧ]', 't', $string);
        $string = mb_ereg_replace('[ÙÚÛÜŨŪŬŮŰŲƯǓǕǗǙǛỤỦỨỪỬỮỰ]', 'U', $string);
        $string = mb_ereg_replace('[ùúûüũūŭůűųưǔǖǘǚǜụủứừửữự]', 'u', $string);
        $string = mb_ereg_replace('[ŴẀẂẄ]', 'W', $string);
        $string = mb_ereg_replace('[ŵẁẃẅ]', 'w', $string);
        $string = mb_ereg_replace('[ÝŶŸỲỸỶỴ]', 'Y', $string);
        $string = mb_ereg_replace('[ýÿŷỹỵỷỳ]', 'y', $string);
        $string = mb_ereg_replace('[ŹŻŽ]', 'Z', $string);
        $string = mb_ereg_replace('[źżž]', 'z', $string);
        return $string;
    }
    /**
     * Check class existence from string
     * 
     * @param string $className   Fullname of class to load
     * @param string $rootFolder  Path to check 
     * @return mixed
     */
    public function getClassFromString($className, $rootFolder = '')
    {
        if (class_exists($className, true)) {
            return new $className();
        }
        $filename = $rootFolder . str_replace('\\', '/', $className) . '.php';
        if (!empty($rootFolder) && file_exists($filename)) {
            require_once($filename);
            $namespaces = explode('\\', $className);
            $className  = array_pop($namespaces);
            return new $className();
        }
        return null;
    }
    /**
     * Converts string to upper case including accents
     * 
     * @param string $str   String to convert
     * @return string
     */
    public function toUpperCase($str)
    {
        return mb_strtoupper($str, 'UTF-8');
    }
    /**
     * Converts string to lower case including accents
     * 
     * @param string $str   String to convert
     * @return string
     */
    public function toLowerCase($str)
    {
        return mb_strtolower($str, 'UTF-8');
    }
    
    /**
     * Compares two strings.
     *
     * @param string $knownString The string of known length to compare against
     * @param string $userInput   The string that the user can control
     *
     * @return Boolean
     */
    public static function equals($knownString, $userInput)
    {
        return StringUtils::equals($knownString, $userInput);
    }
    
    /**
     * Encodes a string in base64 that can be used into URL.
     *
     * @param string $str String to encode
     *
     * @return string  Encoded string
     */
    public function base64Encode($str)
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }
    
    /**
     * Decodes a string.
     *
     * @param string $str String to decode
     *
     * @return string  Decoded string
     */
    public function base64Decode($str)
    {
        return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Compresses a string
     *
     * @param string  $str      String to compress
     * @param boolean $isForUrl
     *
     * @return string  Compressed string
     */
    public function compress($str, $isForUrl = false)
    {
        $compressed = gzcompress($str, 9, ZLIB_ENCODING_DEFLATE);
        return $isForUrl ? $this->base64Encode($compressed) : $compressed;
    }
    
    /**
     * Uncompresses a string.
     *
     * @param string   $str       String to uncompress
     * @param boolean  $isForUrl
     *
     * @return string  Uncompressed string
     */
    public function uncompress($str, $isFromUrl = false)
    {
        $compressed = ($isFromUrl ? $this->base64Decode($str) : $str);
        return gzuncompress($compressed);
    }
}
