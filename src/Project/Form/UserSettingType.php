<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Ilmatar\BaseType;
use Ilmatar\JqGrid;
use Ilmatar\HelperFactory;

class UserSettingType extends BaseType
{
    protected $expected = array('settingList', 'settings');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->mandatories['settingList'] as $code => $value) {
            if ($value['is_editable']) {
                $commonOptions = [
                    'data'     => $this->mandatories['settings'][$code],
                    'required' => true,
                    'mapped'   => false,
                    'label'    => $value['label']
                ];
            
                switch ($value['type']) {
                    case \Entities\Parameter::TYPE_BOOLEAN:
                        $builder->add(
                            $code,
                            'choice',
                            [
                                'choices'  => [0 => JqGrid::JQGRID_FALSE, 1 => JqGrid::JQGRID_TRUE],
                                'expanded' => false,
                                'multiple' => false
                            ] + $commonOptions
                        );
                        break;
                    case \Entities\Parameter::TYPE_STRING:
                        $builder->add(
                            $code,
                            'text',
                            [
                                'max_length' => 256,
                                'trim'       => false
                            ] + $commonOptions
                        );
                        break;
                    case \Entities\Parameter::TYPE_INTEGER:
                        $builder->add(
                            $code,
                            'integer',
                            $commonOptions
                        );
                        break;
                    case \Entities\Parameter::TYPE_FLOAT:
                        $builder->add(
                            $code,
                            'number',
                            $commonOptions
                        );
                        break;
                    case \Entities\Parameter::TYPE_ENUM:
                        $function = sprintf(
                            'getAll%ss',
                            HelperFactory::build('StringHelper')->snakeToCamel($code)
                        );
                        $builder->add(
                            $code,
                            'choice',
                            [
                                'choices'   =>  \Entities\UserSetting::$function(),
                                'expanded' => false,
                                'multiple' => false,
                                'required' => true,
                            ] + $commonOptions
                        );
                        break;
                    default:
                        throw new \Exception("Unknown user setting type");
                }
            }
        }
    }

    public function getName()
    {
        return 'userSetting';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
