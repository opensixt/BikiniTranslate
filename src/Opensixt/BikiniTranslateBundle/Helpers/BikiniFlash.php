<?php
namespace Opensixt\BikiniTranslateBundle\Helpers;

/**
 * BikiniFlash
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class BikiniFlash
{
    /** @var \Symfony\Component\HttpFoundation\Session */
    public $session;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public $translator;

    /**
     * Set save success flash
     */
    public function successSave()
    {
        $this->session->getFlashBag()->add(
            'success',
            $this->translator->trans('save_success')
        );
    }

    /**
     * Set error flash: required fields are empty
     */
    public function errorEmptyRequiredFields()
    {
        $this->error($this->translator->trans('error_empty_required_fields'));
    }

    /**
     * Set error flash
     */
    public function error($message)
    {
        $this->session->getFlashBag()->add(
            'error',
            $message
        );
    }
}
