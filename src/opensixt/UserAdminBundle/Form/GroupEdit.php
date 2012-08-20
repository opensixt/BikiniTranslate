<?php

namespace opensixt\UserAdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

class GroupEdit extends AbstractType
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
        $builder->add('name', 'text', array('label' => $translator->trans('groupname') . ': '))
                ->add(
                    'description',
                    'text',
                    array('label' => $translator->trans('description') . ': ', 'required'  => false)
                )
                ->add(
                    'resources',
                    'entity',
                    array(
                        'label'     => $translator->trans('resources') . ': ',
                        'class'     => 'opensixt\BikiniTranslateBundle\Entity\Resource',
                        'property'  => 'name',
                        'multiple'  => true,
                        'expanded'  => true
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

