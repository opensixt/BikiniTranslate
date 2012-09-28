<?php
namespace Opensixt\SxTranslateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class StatusFreeTextForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'mode',
            'choice',
            array(
                'label'       => 'statusfreetext.status',
                'empty_value' => '',
                'choices'     => $options['mode'],
                'data'        => $options['searchMode'],
                'required'    => true,
            )
        )
        ->add(
            'locale',
            'choice',
            array(
                'label'       => 'freetext.language',
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
            'mode'             => array(),
            'searchMode'       => 0,
            );
    }
}
