<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;

class RoleType extends BaseType
{
    protected $expected = array('action', 'isAdmin');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = '';
        if ($this->mandatories['isAdmin']) {
            $disabled = 'disabled';
        }

        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
            ->add(
                'code',
                'text',
                array(
                   'max_length' => 256,
                   'required'   => true,
                   'trim'       => true,
                   'disabled'   => $disabled
                )
            )
            ->add(
                'description',
                'textarea',
                array(
                   'max_length' => 2048,
                   'trim'       => true,
                   'disabled'   => $disabled,
                   'required'   => false
                )
            )
            ->add(
                'permissions',
                new PermissionType(
                    [
                        'functionalityCodes' => $this->mandatories['functionalityCodes'],
                        'disabled'           => $disabled,
                        'permissions'        => $this->mandatories['permissions']
                    ]
                )
            );

        if (!empty($this->mandatories['kpiList'])) {
            $builder->add(
                'kpis',
                new KpiType(
                    [
                        'kpiList' => $this->mandatories['kpiList'],
                        'kpis'    => $this->mandatories['kpis']
                    ]
                )
            );
        }
    }

    public function getName()
    {
        return 'role';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => '\\Entities\\Role',
            'csrf_protection' => true,
            'csrf_field_name' => '_token'
        ));
    }
}
