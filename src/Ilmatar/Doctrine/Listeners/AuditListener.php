<?php
namespace Ilmatar\Doctrine\Listeners;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventArgs;
use Entities\Audit;
use Ilmatar\Doctrine\Listeners\AuditableInterface;

class AuditListener implements EventSubscriber
{
    protected $excludedFields;
    
    public function __construct()
    {
        $this->excludedSuffixes = ['_at', '_by'];
    
    }

    public function getSubscribedEvents()
    {
        return ['onFlush'];
    }
    
    /**
     * Looks for Auditable objects being updated
     * to track change
     *
     * @param EventArgs $args
     * @return void
     */
    public function onFlush(EventArgs $args)
    {
        $em            = $args->getEntityManager();
        $uow           = $em->getUnitOfWork();
        $classMetadata = $em->getClassMetadata('Entities\Audit');
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof AuditableInterface) {
                foreach ($uow->getEntityChangeSet($entity) as $fieldName => $vals) {
                    if (!in_array(substr($fieldName, -3), $this->excludedSuffixes) && ($vals[0] != $vals[1])) {
                        $isDate   = ($vals[1] instanceof \DateTime);
                        $isEntity = (is_object($vals[1]) ? (false !== strpos(get_class($vals[1]), 'Entities\\')) : false);
                        $history  = new Audit(
                            [
                                'entity_type'   => get_class($entity),
                                'entity_id'     => $entity->getId(),
                                'property_name' => $fieldName,
                                'old_value'     => $this->format($vals[0], $isDate, $isEntity),
                                'new_value'     => $this->format($vals[1], $isDate, $isEntity)
                        
                            ]
                        );
                        $em->persist($history);
                        $uow->computeChangeSet($classMetadata, $history);
                    }
                }
            }
        }
    }
    
    protected function format($value, $isDate, $isEntity)
    {
        if ($isDate) {
            return $value->format(\DateTime::ISO8601);
        }
        if ($isEntity) {
             return $value->getId();
        }
        return (string)$value;
    }
}
