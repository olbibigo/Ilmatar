<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Query extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $user = $em->getRepository('\Entities\User')->findOneById(1);
        
        $query = new \Entities\Query();
        $query->setName('USERS');
        $query->setQuery('SELECT * FROM user');
        $query->setCreator($user);
        $query->setIsExported(false);
        $em->persist($query);    

        $query = new \Entities\Query();
        $query->setName('ROLES');
        $query->setQuery('SELECT * FROM role');
        $query->setCreator($user);
        $query->setVisibility(\Entities\Query::VISIBILITY_ONLY_CREATOR);
        $query->setIsExported(true);
        $query->setExportFormat(\Entities\Query::FORMAT_XML);
        $em->persist($query);    
        
        $query = new \Entities\Query();
        $query->setName('ROLES 2');
        $query->setQuery('SELECT * FROM role');
        $query->setCreator($user);
        $query->setVisibility(\Entities\Query::VISIBILITY_ONLY_CREATOR);
        $query->setIsExported(true);
        $query->setMailList('xxx@xxx.com,yyy@yyy.com');
        $query->setMailRepeats(\Entities\Query::REPEAT_DAILY);
        $query->setExportFormat(\Entities\Query::FORMAT_CSV);
        $em->persist($query);  

        $em->flush();
    }

    public function getDependencies()
    {
        return array('Project\Fixtures\User');
    }
}