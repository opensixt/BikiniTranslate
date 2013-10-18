<?php

namespace Opensixt\UserAdminBundle\Form;

use Opensixt\BikiniTranslateBundle\Entity\Language;
use Opensixt\BikiniTranslateBundle\Entity\Group;
use Opensixt\BikiniTranslateBundle\Entity\Role;
use Opensixt\BikiniTranslateBundle\Repository\LanguageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContext;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

class UserEditForm extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $disableFields = true;
        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            $disableFields = false;
        }

        $builder->add(
                'username',
                'text',
                array(
                    'label'    => 'username',
                    'disabled' => $disableFields,
                )
            )
            ->add(
                'email',
                'email',
                array(
                    'label' => 'email',
                    'disabled' => $disableFields,
                )
            )
            ->add(
                'isactive',
                    'checkbox',
                    array(
                        'label' => 'active',
                        'value' => 1,
                        'required' => false,
                        'disabled' => $disableFields,
                    )
            )
            ->add(
                'userroles',
                'entity',
                array(
                    'label' => 'roles',
                    'class' => Role::ENTITY_ROLE,
                    'property' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'disabled' => $disableFields,
                )
            )
            ->add(
                'userlanguages',
                'entity',
                array(
                    'label' => 'languages',
                    'class' => Language::ENTITY_LANGUAGE,
                    'query_builder' => function(LanguageRepository $lr) {
                        return $lr->createQueryBuilder('l')
                            ->orderBy('l.locale', 'ASC');
                    },
                    'property' => 'locale',
                    'multiple' => true,
                    'expanded' => true,
                    'disabled' => $disableFields,
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
                    'expanded' => true,
                    'disabled' => $disableFields,
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
                    'second_options' => array('label' => 'confirm_password'),
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
