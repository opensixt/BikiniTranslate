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
class CopyResourceForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'res_from',
            'choice',
            array(
                'label'       => 'copy_res_content_from',
                'empty_value' => '',
                'choices'     => $options['resources'],
                'required'    => true,
                'data'        => $options['from'],
            )
        )
        ->add(
            'res_to',
            'choice',
            array(
                'label'       => 'copy_res_content_to',
                'empty_value' => '',
                'choices'     => $options['resources'],
                'required'    => true,
                'data'        => $options['to'],
            )
        )
        ->add(
            'locale',
            'choice',
            array(
                'label'       => 'copy_res_content_lang',
                'empty_value' => 'all_values',
                'choices'     => $options['locales'],
                'required'    => false,
                'data'        => $options['searchLanguage'],
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
            'resources'      => array(),
            'searchLanguage' => 0,
            'locales' => array(),
            'from'    => 0,
            'to'      => 0,
        );
    }
}
