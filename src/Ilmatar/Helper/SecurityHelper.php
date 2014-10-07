<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Ilmatar\HelperFactory;

/**
 * Helper class to manipulate strings.
 *
 */
class SecurityHelper extends BaseHelper
{
    const DEFAULT_KEY = 'QDDwhr5455H8WnCfxJuw';//random string
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];
    
    /**
     * Generates a password
     * 
     * @param integer n$bBytes   Nb of bytes
     * @param boolean $isBase64  (default=false)
     * @return string
     */
    public function generatePassword($nbBytes = 10, $isBase64 = false)
    {
        $generator = new SecureRandom($this->options['seedFile']);
        $random    = $generator->nextBytes($nbBytes);
        return $isBase64 ? base64_encode($random) : $random;
    }
    /**
     * Password encoder getter
     * 
     * @return MessageDigestPasswordEncoder
     */
    public function getEncoder()
    {
        return new MessageDigestPasswordEncoder(
            'sha256',
            true,
            2
        );
    }
    /**
     * Encodes password
     * 
     * @param string  $password Password in clear to encode
     * @param string  $salt
     * @return string
     */
    public function encodePassword($password, $salt = self::DEFAULT_KEY)
    {
        $encoder = $this->getEncoder();
        return $encoder->encodePassword($password, $salt);
    }
    /**
     * Encodes password for a given user
     * 
     * @param \Entities\User $user
     * @param string         $password Password in clear to encode
     * @return string
     */
    public function encodePasswordForUser(\Entities\User $user, $password)
    {
        $encoder = $this->options['security.encoder_factory']->getEncoder($user);
        return $encoder->encodePassword($password, $user->getSalt());
    }
    
    /**
     * Encrypts a string
     * 
     * @param string $encrypt
     * @param string $key
     * @return string
     */
    public function encryptString($input, $textkey = self::DEFAULT_KEY)
    {
        $ivSize    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $securekey = hash('sha256', $textkey, true);
        $iv        = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);
        return HelperFactory::build('StringHelper')->base64Encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $securekey, $input, MCRYPT_MODE_CBC, $iv));
    }

    /**
     * Decrypts a string
     * 
     * @param string $decrypt
     * @param string $key
     * @return string
     */
    public function decryptString($input, $textkey = self::DEFAULT_KEY)
    {
        $ivSize    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $securekey = hash('sha256', $textkey, true);
        $input     = HelperFactory::build('StringHelper')->base64Decode($input);
        $iv        = substr($input, 0, $ivSize);
        $cipher    = substr($input, $ivSize);
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $securekey, $cipher, MCRYPT_MODE_CBC, $iv));
    }
}
