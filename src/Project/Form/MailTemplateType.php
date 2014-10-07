<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;

class MailTemplateType extends BaseType
{
    protected $expected = array('action');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
            ->add(
                'code',
                'text',
                array(
                    'disabled' => true,
                    'required' => false
                )
            )
            ->add(
                'is_active',
                'checkbox',
                array(
                   'required' => false
                )
            )
            ->add(
                'object',
                'text',
                array(
                   'max_length' => 256,
                   'required'   => true,
                   'trim'       => true
                )
            )
            ->add(
                'body',
                'textarea',
                array(
                   'max_length' => 2048,
                   'required'   => true,
                   'trim'       => true
                )
            );

    }

    public function getName()
    {
        return 'mail';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => '\\Entities\\MailTemplate',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'mail_entity'
        ));
    }
}
