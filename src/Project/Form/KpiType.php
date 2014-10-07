<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;

class KpiType extends BaseType
{
    protected $expected = array('kpiList', 'kpis');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->mandatories['kpiList'] as $value) {
            $builder->add(
                $value['label'],
                'choice',
                array(
                    'choices'  => [1 => 'Display', 0 => 'Hide'],
                    'expanded' => true,
                    'multiple' => false,
                    'mapped'   => false,
                    'required' => true,
                    'label'    => $value['description'],
                    'data'     => isset($this->mandatories['kpis'][$value['label']]) ? 1 : 0
                )
            );
        }
    }

    public function getName()
    {
        return 'kpi';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
