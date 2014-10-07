<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Ilmatar\HelperFactory;

class User extends AbstractFixture implements DependentFixtureInterface
{
    const NB_USERS = 100;

    protected $securityHelper;

    public function __construct()
    {
        $this->securityHelper = HelperFactory::build('SecurityHelper');
    }
    
    public function load(ObjectManager $em)
    {
        $role = $em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE);
        
        $user = new \Entities\User();
        $user->setFirstname('Admi');
        $user->setLastname('Nistrateur');
        $user->setPassword($this->securityHelper->encodePassword("tadmintadmin"));//Default salt as in User::getSalt()
        $user->setUsername("tadmin@castelis.com");
        $user->setisActive(true);
        $user->setStreet("Street");
        $user->setZipcode("Zip");
        $user->setCity("City");
        $user->setCountry("FR");
        $user->setPhone("+33(0)123456789");
        $user->setMobile("+33(0)60000000");
        $user->setComment('Administrator account');
        $user->setRole($role);
        $em->persist($user);

        $role = $em->getRepository('\Entities\Role')->findOneByCode('MARKETING');
        
        for ($i = 0;  $i < self::NB_USERS; ++$i) {
            $user = new \Entities\User();
            $user->setFirstname($this->generateString(10));
            $user->setLastname($this->generateString(10));
            //Same salt as defined into User::getSalt() so email
            $username = $this->generateString(10);
            $email    = $username . "@castelis.com";
            $user->setPassword($this->securityHelper->encodePassword($username, $email));
            $user->setGender(\Entities\User::GENDER_FEMALE);
            $user->setUsername($email);
            $user->setisActive(true);
            $user->setRole($role);
            $user->setCity($i);
            $em->persist($user);
        }
        $em->flush();
    }
    
    public function getDependencies()
    {
        return array('Project\Fixtures\Role');
    }
    
    protected function generateString($length)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}