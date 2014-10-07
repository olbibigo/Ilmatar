<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;
use Symfony\Component\Validator\Constraints as Assert;
use Ilmatar\Helper\ImportHelper;

class ImportStep1Type extends BaseType
{
    protected $expected = array('action', 'importableEntities', 'upload.size.max');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
            ->add(
                'entities',
                'choice',
                array(
                    'choices'  => $this->mandatories['importableEntities'],
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                )
            )
            ->add(
                'mode',
                'choice',
                array(
                    'choices'  => array(
                        ImportHelper::MODE_ADD   => 'Add',
                        ImportHelper::MODE_ERASE => 'Erase and Add',
                    ),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                )
            )
            ->add(
                'path',
                'file',
                array(
                    'label'       => 'Path to CSV',
                    'constraints' => array(
                        new Assert\File(
                            array(
                                'maxSize'   => $this->mandatories['upload.size.max'],
                                'mimeTypes' => array('text/csv', 'text/plain')
                            )
                        )
                    )
                )
            );
    }

    public function getName()
    {
        return 'importStep1';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'import_step1_entity'
        ));
    }
}
