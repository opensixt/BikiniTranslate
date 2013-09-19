<?php

namespace Opensixt\UserAdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;

class UserSearchForm extends AbstractType
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
        $builder->add(
            'search',
            'search',
            array(
                'label'       => $this->translator->trans('search_by') . ': ',
                'trim'        => true,
                'required'    => false
            )
        )
        ->add(
            'locale',
            'choice',
            array(
                'label'       => 'language',
                'empty_value' => 'all_values',
                'choices'     => $options['locales'],
                'required'    => false,
                'data'        => $options['searchLanguage']
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

    /**
     * Define default values in option array
     *
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'locales'          => array(),
            'searchLanguage'   => 0,
        );
    }
}
