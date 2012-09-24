<?php
namespace opensixt\SxTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use opensixt\BikiniTranslateBundle\Controller\AbstractController;

use opensixt\SxTranslateBundle\Form\EditMobileForm;
use opensixt\SxTranslateBundle\Entity\Domain;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class MobileController extends AbstractController
{
    const ROUTE_EDIT = '_sxmobile_edit';

    /**
     * intermediate layer
     *
     * @var \opensixt\SxTranslateBundle\IntermediateLayer\HandleMobile
     */
    public $handleMobile;

    /**
     * intermediate layer
     *
     * @var \opensixt\SxTranslateBundle\IntermediateLayer\HandleFreeText
     */
    public $handleText;

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
        $languageId = $this->getLanguageId($locale);
        if (!$languageId) {
            // save current ruote in session (for comeback)
            $this->session->set('targetRoute', self::ROUTE_EDIT);
            // if $locale is not set, redirect to setlocale action
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        $currentLangIsCommonLang = $this->handleMobile
            ->compareCommonAndCurrentLocales($languageId);

        $domains = $this->getDomains();

        // Update texts with entered values
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }

            if (isset($formData['action']) && $formData['action'] == 'save') {
                $textsToSave = $this->getTextsToSaveFromRequest(
                    $formData,
                    'text'
                );

                if (count($textsToSave)) {
                    $this->handleText->updateTexts($textsToSave);
                    $this->bikiniFlash->successSave();
                    return $this->redirectAfterSave(
                        self::ROUTE_EDIT,
                        $page,
                        $locale
                    );
                }
            }
        }

        $searchDomain = $this->getFieldFromRequest('domain');

        if (!empty($searchDomain)) {
            $searchDomains = array($searchDomain);
        } else {
            $searchDomains = array_keys($domains);
        }

        // get search results
        $data = $this->handleMobile->getTranslations(
            $page,
            $languageId,
            $searchDomains
        );

        $ids = array();
        if (!empty($data)) {
            foreach ($data as $elem) {
                $ids[] = $elem->getText()->getId();
            }
        }

        $form = $this->formFactory
            ->create(
                new EditMobileForm(),
                null,
                array(
                    'ids'          => $ids,
                    'domains'      => $domains,
                    'searchDomain' => $searchDomain,
                )
            );

        $templateParam = array(
            'form'   => $form->createView(),
            'texts'  => $data,
            'locale' => $locale,
            'currentLangIsCommonLang' => $currentLangIsCommonLang,
        );

        return $this->render(
            'opensixtSxTranslateBundle:Mobile:editmobile.html.twig',
            $templateParam
        );
    }

    /**
     * Returns array of domains
     *
     * @return array
     */
    protected function getDomains()
    {
        $result = array();

        $data = $this->doctrine
            ->getRepository(Domain::ENTITY_DOMAIN)
                ->findAll();

        if (count($data)) {
            foreach ($data as $elem) {
                $result[$elem->getId()] = $elem->getName();
            }
            uasort(
                $result,
                // @codingStandardsIgnoreStart
                function ($a, $b) {
                // @codingStandardsIgnoreEnd
                    return strcmp($a, $b);
                }
            );
        }

        return $result;
    }
}

