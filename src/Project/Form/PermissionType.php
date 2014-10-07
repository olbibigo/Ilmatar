<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;

class PermissionType extends BaseType
{
    protected $expected = array('functionalityCodes', 'disabled', 'permissions');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->mandatories['functionalityCodes'] as $functionalityCode) {
            $builder->add(
                $functionalityCode,
                'choice',
                array(
                    'choices'     => \Entities\Permission::getAllTypes(),
                    'expanded'    => false,
                    'empty_value' => 'No access',
                    'multiple'    => false,
                    'mapped'      => false,
                    'required'    => false,
                    'disabled'    => $this->mandatories['disabled'],
                    'label'       => $functionalityCode,
                    'data'        => isset($this->mandatories['permissions'][$functionalityCode]) ? $this->mandatories['permissions'][$functionalityCode] : ""
                )
            );
        }
        
    }

    public function getName()
    {
        return 'permission';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
