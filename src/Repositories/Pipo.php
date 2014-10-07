<?php
namespace Repositories;

use Doctrine\ORM\QueryBuilder;
use Ilmatar\JqGrid;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Pipo
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class Pipo extends JqGrid
{
    protected $sums = array(
        'value' => 0
    );
    
    public function getJqGridColNames(array $options = [])
    {
        $columns = parent::getJqGridColNames($options);
        $columns[] = 'User';
        $columns[] = 'functionality';
        return $columns;
    }
    
    public function getJqGridColModels(Translator $translator, UrlGenerator $urlGenerator = null, array $options = [])
    {
        $columns = parent::getJqGridColModels($translator, $urlGenerator, $options);

        $columns[] = array(
            "name"        => 'user',
            "index"       => 'user',
            "search"      => false,
            "editable"    => true,
            "edittype"    => 'select',
            "editrules"   => array("integer" => true, "minValue" => 1, "required" => true),
            "editoptions" => array("dataUrl" => $urlGenerator->generate('pipo-user-select'))
        );
        $columns[] = array(
            "name"        => 'functionality',
            "index"       => 'functionality',
            "search"      => false,
            "editable"    => true,
            "edittype"    => 'select',
            "editrules"   => array("integer" => true, "minValue" => 1, "required" => false),
            "editoptions" => array("dataUrl" => $urlGenerator->generate('pipo-functionality-select'))
        );

        return $columns;
    }
    
    public function formatJqGridRow($entity, Translator $translator, array $options = [])
    {
        $columns = parent::formatJqGridRow($entity, $translator, $options);

        $columns['user'] = $entity->getUser()->getFullname();

        if ($entity->getFunctionality()) {
            $columns['functionality'] = $entity->getFunctionality()->getCode();
        } else {
            $columns['functionality'] = "";
        }
            
        return $columns;
    }
}
