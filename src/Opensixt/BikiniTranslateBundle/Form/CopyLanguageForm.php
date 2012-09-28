<?php
namespace Opensixt\BikiniTranslateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class CopyLanguageForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'lang_from',
            'choice',
            array(
                'label'       => 'copy_lang_content_from', // $translator->trans('')
                'empty_value' => '',
                'choices'     => $options['locales'],
                'required'    => true,
                'data'        => $options['from'],
            )
        )
        ->add(
            'lang_to',
            'choice',
            array(
                'label'       => 'copy_lang_content_to',
                'empty_value' => '',
                'choices'     => $options['locales'],
                'required'    => true,
                'data'        => $options['to']
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
        return 'form';
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
            'locales' => array(),
            'from'    => 0,
            'to'      => 0,
            );
    }
}
