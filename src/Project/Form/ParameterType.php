<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;
use Ilmatar\HelperFactory;

class ParameterType extends BaseType
{
    protected $expected = array('action', 'code', 'type', 'isReadOnly');

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
                'type',
                'text',
                array(
                    'disabled' => true,
                    'required' => false
                )
            )
            ->add(
                'category',
                'text',
                array(
                   'max_length' => 256,
                   'required'   => true,
                   'trim'       => true
                )
            );
            
        switch ($this->mandatories['type']) {
            case \Entities\Parameter::TYPE_BOOLEAN:
                $builder->add(
                    'value',
                    'checkbox',
                    ['disabled' => $this->mandatories['isReadOnly']]
                );
                break;
            case \Entities\Parameter::TYPE_STRING:
                $builder->add(
                    'value',
                    'text',
                    ['disabled' => $this->mandatories['isReadOnly']]
                );
                break;
            case \Entities\Parameter::TYPE_INTEGER:
                $builder->add(
                    'value',
                    'integer',
                    ['disabled' => $this->mandatories['isReadOnly']]
                );
                break;
            case \Entities\Parameter::TYPE_FLOAT:
                $builder->add(
                    'value',
                    'number',
                    ['disabled' => $this->mandatories['isReadOnly']]
                );
                break;
            case \Entities\Parameter::TYPE_ENUM:
                $function = sprintf(
                    'getAll%ss',
                    HelperFactory::build('StringHelper')->snakeToCamel($this->mandatories['code'])
                );
                $builder->add(
                    'value',
                    'choice',
                    array(
                        'choices'   =>  \Entities\Parameter::$function(),
                        'expanded' => false,
                        'multiple' => false,
                        'required' => true,
                    )
                );
                break;
            default:
                throw new \Exception("Unknown parameter type");
        }
    }

    public function getName()
    {
        return 'parameter';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => '\\Entities\\Parameter',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'parameter_entity'
        ));
    }
}
