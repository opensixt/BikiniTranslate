<?php
namespace opensixt\BikiniTranslateBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Bundle\FrameworkBundle\Translation\Translator;
use \Symfony\Component\Form\CallbackValidator;
use \Symfony\Component\Form\FormError;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class EditTextForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'resource',
            'choice',
            array(
              'label'       => 'resource',
              'empty_value' => 'all_values',
              'choices'     => $options['resources'],
              'required'    => false,
              'data'        => $options['searchResource']
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
            'resources'      => array(),
            'searchResource' => 0,
        );
    }
}

