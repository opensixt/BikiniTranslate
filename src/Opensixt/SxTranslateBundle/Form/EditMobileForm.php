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
class EditMobileForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'domain',
            'choice',
            array(
              'label'       => 'editmobile.domain',
              'empty_value' => 'all_values',
              'choices'     => $options['domains'],
              'required'    => false,
              'data'        => $options['searchDomain']
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
            'ids'          => array(),
            'domains'      => array(),
            'searchDomain' => 0,
        );
    }
}
