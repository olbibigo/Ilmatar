<?php
 
namespace Ilmatar;
 
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Ilmatar\Application;
 
/**
* References Doctrine connections and entity/document managers.
*/
class ManagerRegistry extends AbstractManagerRegistry
{
    /**
    * @var Application
    */
    protected $container;
     
    protected function getService($name)
    {
        return $this->container[$name];
    }
     
    protected function resetService($name)
    {
        unset($this->container[$name]);
    }
     
    public function getAliasNamespace($alias)
    {
        throw new \BadMethodCallException('Namespace aliases not supported.');
    }
     
    public function setContainer(Application $container)
    {
        $this->container = $container;
    }
}
