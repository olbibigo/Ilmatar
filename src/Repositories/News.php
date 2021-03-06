<?php

namespace Repositories;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Ilmatar\JqGrid;

/**
 * News
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class News extends JqGrid
{
    public function getJqGridColModels(Translator $translator, UrlGenerator $urlGenerator = null, array $options = [])
    {
        $columns = parent::getJqGridColModels($translator, $urlGenerator, $options);

        foreach ($columns as $idx => $column) {
            if ('created_at' == $column['name']) {
                $columns[$idx]['hidden'] = false;
            } elseif ('created_by' == $column['name']) {
                $columns[$idx]['hidden'] = false;
            }
        }
        return $columns;
    }
    
    public function getLatestNews()
    {
        return $this
            ->createQueryBuilder('n')
            ->orderBy('n.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
}
