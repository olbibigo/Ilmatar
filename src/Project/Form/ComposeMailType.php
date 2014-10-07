<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;
use Symfony\Component\Validator\Constraints as Assert;

class ComposeMailType extends BaseType
{
    protected $expected = array('action');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
            ->add(
                'tos',
                'text',
                array(
                   'max_length'  => 256,
                   'required'    => true,
                   'trim'        => true,
                   'label'       => 'Recipients',
                   'constraints' => array(new Assert\NotBlank())
                )
            )
            ->add(
                'subject',
                'text',
                array(
                   'max_length'  => 256,
                   'required'    => true,
                   'trim'        => true,
                   'label'       => 'Subject',
                   'constraints' => array(new Assert\NotBlank())
                )
            )
            ->add(
                'body',
                'textarea',
                array(
                   'max_length'  => 2048,
                   'required'    => true,
                   'trim'        => true,
                   'label'       => 'Body',
                   'constraints' => array(new Assert\NotBlank())
                )
            );
    }

    public function getName()
    {
        return 'composeMail';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'compose_mail_entity'
        ));
    }
}
