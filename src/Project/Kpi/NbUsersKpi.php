<?php
namespace Project\Kpi;

use Ilmatar\BaseKpi;

class NbUsersKpi extends BaseKpi
{
    public function compute()
    {
        $query = $this->em->createQuery('SELECT COUNT(u.id) FROM Entities\User u');
        return intval($query->getSingleScalarResult());
    }
}
