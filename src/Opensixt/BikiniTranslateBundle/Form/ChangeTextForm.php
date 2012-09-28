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
class ChangeTextForm extends AbstractType
{
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
                'label'       => 'search_by',
                'trim'        => true,
                'data'        => $options['searchPhrase'],
                'required'    => true,
            )
        )
        ->add(
            'resource',
            'choice',
            array(
                'label'       => 'with_resource',
                'empty_value' => 'all_values',
                'choices'     => $options['resources'],
                'data'        => $options['searchResource'],
                'required'    => false,
            )
        )
        ->add(
            'locale',
            'choice',
            array(
                'label'       => 'with_language',
                'empty_value' => '',
                'choices'     => $options['locales'],
                'data'        => $options['searchLanguage'],
                'required'    => true,
            )
        )
        ->add('action', 'hidden');

        if (!empty($options['ids'])) {
            foreach ($options['ids'] as $id) {
                $builder->add(
                    'text_' . $id,
                    'textarea',
                    array(
                        'trim' => true,
                        'required' => false,
                    )
                );
            }
        }
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
            'ids'            => array(),
            'searchPhrase'   => '',
            'resources'      => array(),
            'searchResource' => 0,
            'locales'        => array(),
            'searchLanguage' => 0,
        );
    }
}
