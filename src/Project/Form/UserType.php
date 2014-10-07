<?php
namespace Project\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Ilmatar\BaseType;
use Ilmatar\JqGrid;
use Ilmatar\HelperFactory;

class UserType extends BaseType
{
    protected $expected = array('isLdapUser', 'action', 'userId', 'locale');

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setAction($this->mandatories['action'])
            ->setMethod('POST')
            ->add(
                'gender',
                'choice',
                array(
                    'choices'   =>  \Entities\User::getAllGenders(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => true,
                )
            )
            ->add(
                'firstname',
                'text',
                array(
                   'max_length' => 256,
                   'required'   => true,
                   'trim'       => true
                )
            )
            ->add(
                'lastname',
                'text',
                array(
                   'max_length' => 256,
                   'required'   => true,
                   'trim'       => true
                )
            )
            ->add(
                'role',
                'entity',
                array(
                    'expanded' => false,
                    'multiple' => false,
                    'class'    => 'Entities\Role',
                    'property' => 'code',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.code', 'ASC');
                    }
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
                'comment',
                'textarea',
                array(
                   'max_length' => 2048,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'street',
                'text',
                array(
                   'max_length' => 256,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'zipcode',
                'text',
                array(
                   'max_length' => 256,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'city',
                'text',
                array(
                   'max_length' => 256,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'country',
                'choice',
                array(
                    'choices'   =>  HelperFactory::build("IntlHelper", ["locale" => $this->mandatories['locale']])->getCountryNames(),
                    'expanded' => false,
                    'multiple' => false
                )
            )
            ->add(
                'phone',
                'text',
                array(
                   'max_length' => 256,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'mobile',
                'text',
                array(
                   'max_length' => 256,
                   'trim'       => true,
                   'required'   => false
                )
            )
            ->add(
                'settings',
                new UserSettingType(
                    [
                        'settings'    => $this->mandatories['settings'],
                        'settingList' => $this->mandatories['settingList']
                    ]
                )
            );
            
        if (!$this->mandatories['isLdapUser']) {
            $builder
                ->add(
                    'username',
                    'email',
                    array(
                        'max_length' => 256,
                        'required'   => true,
                        'trim'       => true,
                        'attr'       => array('autocomplete' => 'off')
                    )
                )
                ->add(
                    'password',
                    'repeated',
                    array(
                        'type' => 'password',
                        'invalid_message' => 'Passwords must match',
                        'options' => array(
                            'always_empty' => true,
                            'max_length'   => 32,
                            //Password is required only if new user
                            'required'     => (JqGrid::ID_NEW_ENTITY == $this->mandatories['userId']),
                            'trim'         => true
                        ),
                        'first_options'  => array(
                            'label' => 'Password',
                            'attr'  => array('autocomplete' => 'off')
                        ),
                        'second_options' => array(
                            'label' => 'Password (validation)',
                            'attr'  => array('autocomplete' => 'off')
                        )
                    )
                );
        }
    }

    public function getName()
    {
        return 'user';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => '\\Entities\\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'       => 'user_entity'
        ));
    }
}
