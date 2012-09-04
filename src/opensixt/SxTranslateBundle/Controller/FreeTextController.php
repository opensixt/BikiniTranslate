<?php
namespace opensixt\SxTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use opensixt\BikiniTranslateBundle\Controller\AbstractController;
use opensixt\SxTranslateBundle\Form\AddFreeTextForm;
use opensixt\BikiniTranslateBundle\Entity\Language;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class FreeTextController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \opensixt\BikiniTranslateBundle\IntermediateLayer\HandleFreeText
     */
    public $handleFreeText;

    /** @var Doctrine\Bundle\DoctrineBundle\Registry */
    public $doctrine;

    /** @var string */
    public $toolLanguage;

    /** @var string */
    public $commonLanguage;

    /**
     * add new free text
     *
     * @return Response A Response instance
     */
    public function addfreetextAction()
    {
        $locales = $this->getUserLocales();

        $flipLocales = array_flip($locales);
        if (!empty($flipLocales[$this->toolLanguage])) {
            unset($locales[$flipLocales[$this->toolLanguage]]);
        }
        if (!empty($flipLocales[$this->commonLanguage])) {
            unset($locales[$flipLocales[$this->commonLanguage]]);
        }

        $frmLanguage = $this->getFieldFromRequest('locale');
        $frmTitle = $this->getFieldFromRequest('headline');
        $frmText = $this->getFieldFromRequest('text');

        if (!empty($frmLanguage) && empty($locales[$frmLanguage])) {
            $frmLanguage = 0;
        }

        // Update texts with entered values
        if ($this->request->getMethod() == 'POST') {
            if (!empty($frmLanguage) && !empty($frmTitle) && !empty($frmText)) {

                $toolLanguageId = $this->doctrine
                    ->getRepository(Language::ENTITY_LANGUAGE)
                        ->findOneByLocale($this->toolLanguage)->getId();

                $commonLanguageId = $this->doctrine
                    ->getRepository(Language::ENTITY_LANGUAGE)
                        ->findOneByLocale($this->commonLanguage)->getId();

                $this->handleFreeText->addFreeText(
                    $frmTitle,
                    $frmText,
                    $toolLanguageId
                );
                $this->handleFreeText->addFreeText(
                    $frmTitle,
                    '',
                    $commonLanguageId
                );
                $this->handleFreeText->addFreeText(
                    $frmTitle,
                    '',
                    $frmLanguage
                );

                $this->session->getFlashBag()->add(
                    'notice',
                    $this->translator->trans('save_success')
                );
            } else {
                $this->session->getFlashBag()->add(
                    'error',
                    $this->translator->trans('error_empty_required_fields')
                );
            }
        }

        $form = $this->formFactory
            ->create(
                new AddFreeTextForm(),
                null,
                array(
                    'headline' => $frmTitle,
                    'text' => $frmText,
                    'locales'  => $locales,
                    'frmLanguage' => $frmLanguage,
                )
            );

        $templateParam = array(
            'form'  => $form->createView(),
        );

        return $this->render(
            'opensixtSxTranslateBundle:FreeText:addfreetext.html.twig',
            $templateParam
        );
    }
}

