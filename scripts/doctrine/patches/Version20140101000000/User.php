<?php
namespace Version20140101000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Ilmatar\HelperFactory;

class User extends AbstractFixture implements DependentFixtureInterface
{
    const ADMIN_USERNAME = "tadmin@castelis.com";
    const ADMIN_PASSWORD = "tadmintadmin";
    
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
        $user->setPassword($this->securityHelper->encodePassword(self::ADMIN_PASSWORD));//Default salt as in User::getSalt()
        $user->setUsername(self::ADMIN_USERNAME);
        $user->setisActive(true);
        $user->setStreet("Street");
        $user->setZipcode("Zip");
        $user->setCity("City");
        $user->setCountry("FR");
        $user->setPhone("+33(0)123456789");
        $user->setMobile("+33(0)60000000");
        $user->setComment('Technical administrator account');
        $user->setRole($role);
        $em->persist($user);

        $em->flush();
    }
    
    public function getDependencies()
    {
        return array('Version20140101000000\Role');
    }
}
