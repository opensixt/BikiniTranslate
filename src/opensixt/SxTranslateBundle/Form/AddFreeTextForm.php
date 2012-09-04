<?php
namespace opensixt\SxTranslateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class AddFreeTextForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'headline',
            'text',
            array(
                'label'       => 'freetext.title',
                'trim'        => true,
                'data'        => $options['headline'],
                'required'    => true,
            )
        )
        ->add(
            'text',
            'textarea',
            array(
                'label'       => 'freetext.text',
                'trim'        => true,
                'data'        => $options['text'],
                'required'    => true,
            )
        )
        ->add(
            'locale',
            'choice',
            array(
                'label'       => 'freetext.language',
                'empty_value' => '',
                'choices'     => $options['locales'],
                'data'        => $options['frmLanguage'],
                'required'    => true,
            )
        )
        ->add('action', 'hidden');
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
            'headline' => '',
            'text'     => '',
            'locales'  => array(),
            'frmLanguage' => 0,
        );
    }
}

