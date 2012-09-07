<?php
namespace opensixt\SxTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use opensixt\BikiniTranslateBundle\Controller\AbstractController;
use opensixt\SxTranslateBundle\Form\AddFreeTextForm;
use opensixt\SxTranslateBundle\Form\EditFreeTextForm;
use opensixt\BikiniTranslateBundle\Entity\Language;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class FreeTextController extends AbstractController
{
    const ROUTE_EDIT = '_sxfreetext_edit';

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
    public function addAction()
    {
        $locales = $this->getUserLocales();

        $flipLocales = array_flip($locales);
        if (!empty($flipLocales[$this->toolLanguage])) {
            unset($locales[$flipLocales[$this->toolLanguage]]);
        }
        if (!empty($flipLocales[$this->commonLanguage])) {
            unset($locales[$flipLocales[$this->commonLanguage]]);
        }

        $frmLanguages = $this->getFieldFromRequest('locale');
        $frmTitle = $this->getFieldFromRequest('headline');
        $frmText = $this->getFieldFromRequest('text');

        // unset all unavailable locales
        if (!empty($frmLanguages)) {
            for ($i = 0; $i < count($frmLanguages); $i++) {
                if (empty($locales[$frmLanguages[$i]])) {
                    unset($frmLanguages[$i]);
                }
            }
        } else {
            $frmLanguages = array();
        }

        // Update texts with entered values
        if ($this->request->getMethod() == 'POST') {
            if (count($frmLanguages) && !empty($frmTitle) && !empty($frmText)) {

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

                foreach ($frmLanguages as $frmLanguage) {
                    $this->handleFreeText->addFreeText(
                        $frmTitle,
                        '',
                        $frmLanguage
                    );
                }
                $this->bikiniFlash->successSave();
            } else {
                $this->bikiniFlash->errorEmptyRequiredFields();
            }
        }

        $form = $this->formFactory
            ->create(
                new AddFreeTextForm(),
                null,
                array(
                    'headline'     => $frmTitle,
                    'text'         => $frmText,
                    'locales'      => $locales,
                    'frmLanguages' => $frmLanguages,
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

    /**
     * edittext Action
     *
     * @param string $locale
     * @param int $page
     * @return Response A Response instance
     */
    public function editAction($locale, $page = 1)
    {
        $languageId = $this->getLanguageId($locale);
        if (!$languageId) {
            // save current ruote in session (for comeback)
            $this->session->set('targetRoute', self::ROUTE_EDIT);
            // if $locale is not set, redirect to setlocale action
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        // Update texts with entered values
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);

            $textsToSave = $this->getTextsToSaveFromRequest(
                $formData,
                'text'
            );
            if (count($textsToSave)) {
                $this->handleFreeText->updateTexts($textsToSave);
                $this->bikiniFlash->successSave();
                return $this->redirectAfterSave(
                    self::ROUTE_EDIT,
                    $page,
                    $locale
                );
            }
        }

        // get search results
        $data = $this->handleFreeText->getMissingTranslations(
            $page,
            $languageId
        );

        $ids = $this->getIdsFromResults($data);

        $form = $this->formFactory
            ->create(
                new EditFreeTextForm(),
                null,
                array(
                    'ids' => $ids,
                )
            );

        $templateParam = array(
            'form'   => $form->createView(),
            'texts'  => $data,
            'locale' => $locale,
        );

        return $this->render(
            'opensixtSxTranslateBundle:FreeText:editfreetext.html.twig',
            $templateParam
        );
    }
}

