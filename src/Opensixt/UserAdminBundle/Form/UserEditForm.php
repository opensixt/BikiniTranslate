<?php

namespace Opensixt\UserAdminBundle\Form;

use Opensixt\BikiniTranslateBundle\Entity\Language;
use Opensixt\BikiniTranslateBundle\Entity\Group;
use Opensixt\BikiniTranslateBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

class UserEditForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', 'text', array('label' => 'username'))
                ->add('email', 'email', array('label' => 'email'))
                ->add('isactive', 'checkbox', array('label' => 'active', 'value' => 1, 'required' => false))
                ->add(
                    'userroles',
                    'entity',
                    array(
                        'label' => 'roles',
                        'class' => Role::ENTITY_ROLE,
                        'property' => 'label',
                        'multiple' => true,
                        'expanded' => true
                    )
                )
                ->add(
                    'userlanguages',
                    'entity',
                    array(
                        'label' => 'languages',
                        'class' => Language::ENTITY_LANGUAGE,
                        'property' => 'locale',
                        'multiple' => true,
                        'expanded' => true
                    )
                )
                ->add(
                    'usergroups',
                    'entity',
                    array(
                        'label' => 'groups',
                        'class' => Group::ENTITY_GROUP,
                        'property' => 'name',
                        'multiple' => true,
                        'expanded' => true
                    )
                )
                ->add(
                    'password',
                    'repeated',
                    array(
                        'type' => 'password',
                        'required' => 'create' === $options['intention'],
                        'invalid_message' => 'Passwords have to be equal',
                        'first_options' => array('label' => 'new_password'),
                        'second_options' => array('label' => 'confirm_password')
                    )
                );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return '';
    }
}
