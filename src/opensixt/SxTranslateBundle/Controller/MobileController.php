<?php
namespace opensixt\SxTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use opensixt\BikiniTranslateBundle\Controller\AbstractController;

use opensixt\SxTranslateBundle\Form\EditMobileForm;
use opensixt\SxTranslateBundle\Form\ChangeMobileForm;
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
     * @var \opensixt\SxTranslateBundle\IntermediateLayer\SearchMobile
     */
    public $handleSearch;

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

        // array with all domains or with only search domain
        $searchDomains = $this->getSearchDomains();

        // get search results
        $data = $this->handleMobile->getTranslations(
            $page,
            $languageId,
            $searchDomains
        );

        $ids = $this->getIdsFromResults($data);

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
     * change Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function changeAction($page)
    {
        $domains = $this->getDomains();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchPhrase   = $this->getFieldFromRequest('search');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchDomain   = $this->getFieldFromRequest('domain');

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

                    $params = array();
                    if (!empty($searchPhrase)) {
                        $params['search'] = $searchPhrase;
                    }
                    if (!empty($searchDomain)) {
                        $params['domain'] = $searchDomain;
                    }

                    return $this->redirectAfterSave(
                        '_sxmobile_change',
                        $page,
                        $searchLanguage,
                        $params
                    );
                }
            }
        }

        if (strlen($searchPhrase)) {
            // set search parameters
            $searchDomains = $this->getSearchDomains();
            $this->handleSearch->setSearchParameters($searchPhrase);

            // get search results
            $results = $this->handleSearch->getData(
                $page,
                $searchLanguage,
                $searchDomains
            );

            // ids of texts for textareas
            $ids = $this->getIdsFromResults($results);
        }

        $form = $this->formFactory
            ->create(
                new ChangeMobileForm(),
                null,
                array(
                    'searchPhrase'    => $searchPhrase,
                    'searchDomain'    => $searchDomain,
                    'domains'         => $domains,
                    'searchLanguage'  => $searchLanguage,
                    'locales'         => $locales,
                    'ids'             => !empty($ids) ? $ids : array(),
                )
            );

        $templateParam = array(
            'form'         => $form->createView(),
            'search'       => urlencode($searchPhrase),
            'searchPhrase' => $searchPhrase,
            'locale'       => $searchLanguage,
            'domain'       => $searchDomain,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'opensixtSxTranslateBundle:Mobile:changemobile.html.twig',
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

    /**
     * Get search resources
     *
     * @return array
     */
    protected function getSearchDomains()
    {
        // retrieve resource from request
        $searchDomain = $this->getFieldFromRequest('domain');
        $domains = $this->getDomains();

        if (!empty($searchDomain) && !empty($domains[$searchDomain])) {
            // if $searchDomain is set and available
            $searchDomains = array($searchDomain);
        } else {
            // all available resources
            $searchDomains = array_keys($domains);
        }
        return $searchDomains;
    }

    /**
     * Get Array of Ids from $results
     *
     * @param ArrayCollection $results
     * @return array
     */
    protected function getIdsFromResults($results)
    {
        $ids = array();
        if (!empty($results)) {
            foreach ($results as $elem) {
                $ids[] = $elem->getText()->getId();
            }
        }

        return $ids;
    }
}

