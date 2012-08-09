<?php

namespace opensixt\UserAdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

class UserEdit extends AbstractType
{
    /** @var Translator */
    public $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $this->translator;
        $builder->add('username', 'text', array('label' => $translator->trans('username') . ': '))
                ->add('email', 'email', array('label' => $translator->trans('email') . ': '))
                ->add('isactive', 'checkbox', array('label' => $translator->trans('active') . ': ',
                                                    'value' => 1,
                                                    'required' => false))
                ->add('userroles', 'entity', array('label' => $translator->trans('roles') . ': ',
                                                   'class' => 'opensixt\BikiniTranslateBundle\Entity\Role',
                                                   'property' => 'label',
                                                   'multiple' => true,
                                                   'expanded' => true))
                ->add('userlanguages', 'entity', array('label' => 'languages',
                                                       'class' => 'opensixt\BikiniTranslateBundle\Entity\Language',
                                                       'property' => 'locale',
                                                       'multiple' => true,
                                                       'expanded' => true))
                ->add('usergroups', 'entity', array('label' => 'groups',
                                                    'class' => 'opensixt\BikiniTranslateBundle\Entity\Groups',
                                                    'property' => 'name',
                                                    'multiple' => true,
                                                    'expanded' => true))
                ->add('password', 'repeated', array('type' => 'password',
                                                    'first_options' => array('property_path' => false,
                                                                             'required' => false,
                                                                             'label' => $translator->trans('new_password') . ': '),
                                                    'second_options' => array('label' => $translator->trans('confirm_password') . ': ',
                                                                              'property_path' => false,
                                                                              'required' => false)));
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