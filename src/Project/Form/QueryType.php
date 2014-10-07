<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;
use Entities\Query;

class QueryType extends BaseType
{
    protected $expected = array('action');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
            ->add(
                'visibility',
                'choice',
                array(
                    'choices'  => Query::getAllVisibilities(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true
                )
            )
            ->add(
                'name',
                'text',
                array(
                   'max_length' => 256,
                   'required'   => true,
                   'trim'       => true
                )
            )
            ->add(
                'comment',
                'textarea',
                array(
                   'max_length' => 1024,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'query',
                'textarea',
                array(
                   'max_length' => 2048,
                   'required'   => true,
                   'trim'       => true
                )
            )
            ->add(
                'is_exported',
                'checkbox',
                array(
                   'required' => false
                )
            )
            ->add(
                'mail_list',
                'text',
                array(
                   'max_length' => 2048,
                   'required'   => false,
                   'trim'       => true
                )
            )
            ->add(
                'mail_repeats',
                'choice',
                array(
                    'choices'  => Query::getAllRepeats(),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                )
            )
            ->add(
                'export_format',
                'choice',
                array(
                    'choices'  => Query::getAllExportFormats(),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                )
            )
            ->add(
                'mail_offset',
                'hidden'
            );
    }

    public function getName()
    {
        return 'query';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => '\\Entities\\Query',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'query_entity'
        ));
    }
}
