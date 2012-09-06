<?php
namespace opensixt\SxTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use opensixt\BikiniTranslateBundle\Controller\AbstractController;
use opensixt\SxTranslateBundle\Form\EditFreeTextForm;
use opensixt\BikiniTranslateBundle\Entity\Language;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class EditFreeTextController extends AbstractController
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
     * edittext Action
     *
     * @param string $locale
     * @param int $page
     * @return Response A Response instance
     */
    public function editAction($locale, $page = 1)
    {
        // if $locale is not set, redirect to setlocale action
        if (!$locale || $locale == 'empty') {
            // store an attribute for reuse during a later user request
            $this->session->set('targetRoute', '_sxfreetext_edit');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        } else {
            // get language id with locale
            $userLang = array_flip($this->getUserLocales());
            $languageId = isset($userLang[$locale]) ? $userLang[$locale] : 0;
        }
        if (!$languageId) {
            $this->session->set('targetRoute', '_sxfreetext_edit');
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
            'form'                    => $form->createView(),
            'texts'                   => $data,
            'locale'                  => $locale,
        );

        return $this->render(
            'opensixtSxTranslateBundle:FreeText:editfreetext.html.twig',
            $templateParam
        );
    }
}

