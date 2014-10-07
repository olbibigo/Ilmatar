<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;
use Symfony\Component\Validator\Constraints as Assert;

class ImportStep2Type extends BaseType
{
    protected $expected = array('action', 'upload.size.max');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
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
        return 'importStep2';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'import_step2_entity'
        ));
    }
}
