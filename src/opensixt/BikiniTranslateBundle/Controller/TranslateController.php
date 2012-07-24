<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;
use opensixt\BikiniTranslateBundle\Repository\TextRepository;

class TranslateController extends Controller
{

    /**
     * Pagination limit
     * @var int
     */
    private $_paginationLimit;

    /**
     * Pagination limit for searchstring
     * @var int
     */
    private $_paginationLimitSearch;


    public function __construct() {
        $this->_paginationLimit = 15;
        $this->_paginationLimitSearch = 15;
    }

    /**
     * translate index Action
     *
     * @return Response A Response instance
     */
    public function indexAction()
    {
        //print_r($this->getLocaleForLogedUser());
        return $this->render('opensixtBikiniTranslateBundle:Translate:index.html.twig');
    }

    /**
     * edittext Action
     *
     * @param string $locale
     * @param int $page
     * @return Response A Response instance
     */
    public function edittextAction($locale, $page = 1)
    {
        $session = $this->get('session');

        // if $locale is not set, redirect to setlocale action
        if (!$locale || $locale == 'empty') {
            // store an attribute for reuse during a later user request
            $session->set('targetRoute', '_translate_edittext');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        } else {
            // get language id with locale
            $userLang = array_flip($this->getUserLocales());
            $languageId = isset($userLang[$locale]) ? $userLang[$locale] : 0;
        }

        if (!$languageId) {
            $session->set('targetRoute', '_translate_edittext');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        $request = $this->getRequest();
        $translator = $this->get('translator');

        $em = $this->getDoctrine()->getEntityManager();
        $tr = $em->getRepository('opensixtBikiniTranslateBundle:Text');

        $commonLang = $this->container->getParameter('common_language');
        $tr->setCommonLanguage($commonLang);

        $textRevisionControl = $this->container->getParameter('text_revision_control');
        $tr->setTextRevisionControl($textRevisionControl);

        $currentLangIsCommonLang = false;
        if ($commonLang == $locale) {
            $currentLangIsCommonLang = true;
        }

        // Update texts with entered values
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->get('form');
            if (isset($formData)) {
                if (isset($formData['action']) && $formData['action'] == 'search') {
                    $page = 1;
                }

                if (isset($formData['action']) && $formData['action'] == 'save') {
                    foreach ($formData as $key => $value) {
                        // for all textareas with name 'text_[number]'
                        if (preg_match("/text_([0-9]+)/", $key, $matches) && strlen($value)) {
                            $tr->updateText($matches[1], $value);
                        }
                    }
                }
            }
        }

        $resources = $this->getUserResources(); // all available resources

        $searchResource = $this->getFieldFromRequest('resource');
        if ($searchResource) {
            $searchResources = array($searchResource);
        } else {
            $searchResources = array_keys($resources);
        }

        $textCount = $tr->getTextCount(
            TextRepository::MISSING_TRANS_BY_LANG,
            $languageId,
            $searchResources);

        $pagination = new Pagination($textCount, $this->_paginationLimit, $page);
        $paginationBar = $pagination->getPaginationBar();

        $texts = $tr->getMissingTranslations(
            $this->_paginationLimit,
            $pagination->getOffset()
            );

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('resource', 'choice', array(
                  'label'       => $translator->trans('resource') . ': ',
                  'empty_value' => $translator->trans('all_values'),
                  'choices'     => $resources,
                  'required'    => false,
                  'data'        => $searchResource
                ))
            ->add('action', 'hidden');

        // define textareas for any text
        foreach ($texts as $txt) {
            $formBuilder->add('text_' . $txt['id'] , 'textarea', array(
                'trim' => true,
                'required' => false,
            ));
        }
        $form = $formBuilder->getForm();

        $templateParam = array(
            'form'                    => $form->createView(),
            'texts'                   => $texts,
            'paginationbar'           => $paginationBar,
            'locale'                  => $locale,
            'currentLangIsCommonLang' => $currentLangIsCommonLang,
        );

        if ($searchResource) {
            $templateParam['resource'] = $searchResource;
        }

        return $this->render('opensixtBikiniTranslateBundle:Translate:edittext.html.twig',
            $templateParam
            );
    }

    /**
     * setlocale Action
     *
     * @return Response A Response instance
     */
    public function setlocaleAction()
    {
        $session = $this->get('session');

        $locales = $this->getUserLocales();
        if (count($locales) == 1) {
            return $this->redirect($this->generateUrl(
                $session->get('targetRoute') ? : '_translate_home',
                array('locale' => $locales[0])
                ));
        }

        $request = $this->getRequest();
        $translator = $this->get('translator');

        $form = $this->createFormBuilder()
            ->add('locale', 'choice', array(
                    'label'     => $translator->trans('please_choose_locale') . ': ',
                    'empty_value' => '',
                    'choices'   => $locales,
                ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {

                if ($form->get('locale')->getData()) {
                    //echo $form->get('locale')->getData();
                    $localeId = $form->get('locale')->getData();
                    $locale = isset($locales[$localeId]) ? $locales[$localeId] : '';

                    return $this->redirect($this->generateUrl(
                        $session->get('targetRoute') ? : '_translate_home',
                        array('locale' => $locale)
                        ));
                }

            } else {
                var_dump($form->getErrors());
            }
        }

        return $this->render('opensixtBikiniTranslateBundle:Translate:setlocale.html.twig',
            array(
                'form' => $form->createView(),
            ));
    }

    /**
     * searchstring Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function searchstringAction($page = 1)
    {
        $translator = $this->get('translator');

        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();
        $mode = array(
            TextRepository::SEARCH_EXACT => $translator->trans('exact_match'),
            TextRepository::SEARCH_LIKE  => $translator->trans('like'),
        );

        // retrieve request parameters
        $searchPhrase = $this->getFieldFromRequest('search');
        $searchResource = $this->getFieldFromRequest('resource');
        $searchMode = $this->getFieldFromRequest('mode');

        if (strlen($searchResource)) {
            $searchResources = array($searchResource);
        } else {
            $searchResources = array_keys($resources);
        }

        if (strlen($searchPhrase)) {
            $em = $this->getDoctrine()->getEntityManager();
            $tr = $em->getRepository('opensixtBikiniTranslateBundle:Text');

            // use tool_language (default language) for search
            $toolLang = $this->container->getParameter('tool_language');

            // set search parameters
            $tr->setSearchParameters($searchPhrase, $searchMode);

            $textCount = $tr->getTextCount(
                TextRepository::SEARCH_PHRASE_BY_LANG,
                $tr->getIdByLocale($toolLang),
                $searchResources);

            $pagination = new Pagination(
                $textCount,
                $this->_paginationLimitSearch,
                $page);
            $paginationBar = $pagination->getPaginationBar();

            // get search results
            $searchResults = $tr->getSearchResults(
                $this->_paginationLimitSearch,
                $pagination->getOffset());
            //print_r($searchResults);
        }

        $form = $this->createFormBuilder()
            ->add('search', 'search', array(
                    'label'       => $translator->trans('search_by') . ': ',
                    'trim'        => true,
                    'data'        => $searchPhrase
                ))
            ->add('resource', 'choice', array(
                    'label'       => $translator->trans('with_resource') . ': ',
                    'empty_value' => $translator->trans('all_values'),
                    'choices'     => $resources,
                    'required'    => false,
                    'data'        => $searchResource
                ))
            ->add('mode', 'choice', array(
                    'label'       => $translator->trans('search_method') . ': ',
                    'empty_value' => '',
                    'choices'     => $mode,
                    'data'        => $searchMode
                ))
            ->getForm();

        $templateParam = array(
            'form'          => $form->createView(),
            'search'        => urlencode($searchPhrase),
            'searchPhrase'  => $searchPhrase,
            'mode'          => $searchMode,
            'resource'      => $searchResource,
        );

        if (isset($paginationBar)) {
            $templateParam['paginationbar'] = $paginationBar;
        }

        if (isset($searchResults)) {
            $templateParam['searchResults'] = $searchResults;
        }

        return $this->render('opensixtBikiniTranslateBundle:Translate:searchstring.html.twig',
            $templateParam);
    }

    /**
     * changetext Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function changetextAction($page)
    {
        $translator = $this->get('translator');

        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchPhrase = $this->getFieldFromRequest('search');
        $searchLocale = $this->getFieldFromRequest('locale');
        $searchResource = $this->getFieldFromRequest('resource');

        if (strlen($searchResource)) {
            $searchResources = array($searchResource);
        } else {
            $searchResources = array_keys($resources);
        }

        if (strlen($searchPhrase)) {
            $em = $this->getDoctrine()->getEntityManager();
            $tr = $em->getRepository('opensixtBikiniTranslateBundle:Text');

            // set search parameters
            $tr->setSearchParameters($searchPhrase);

            $textCount = $tr->getTextCount(
                TextRepository::SEARCH_PHRASE_BY_LANG,
                $searchLocale,
                $searchResources);

            $pagination = new Pagination(
                $textCount,
                $this->_paginationLimitSearch,
                $page);
            $paginationBar = $pagination->getPaginationBar();

            // get search results
            $searchResults = $tr->getSearchResults(
                $this->_paginationLimitSearch,
                $pagination->getOffset());
        }


        $formBuilder = $this->createFormBuilder();

        // define textareas for any text
        if (!empty($searchResults)){
            foreach ($searchResults as $txt) {
                $formBuilder->add('text_' . $txt['id'] , 'textarea', array(
                    'trim' => true,
                    'required' => false,
                ));
            }
        }

        $formBuilder->add('search', 'text', array(
                    'label'       => $translator->trans('search_by') . ': ',
                    'trim'        => true,
                    'data'        => $searchPhrase,
                ))
            ->add('resource', 'choice', array(
                    'label'       => $translator->trans('with_resource') . ': ',
                    'empty_value' => $translator->trans('all_values'),
                    'choices'     => $resources,
                    'data'        => $searchResource,
                    'required'    => false,
                ))
            ->add('locale', 'choice', array(
                    'label'       => $translator->trans('with_language') . ': ',
                    'empty_value' => '',
                    'choices'     => $locales,
                    'data'        => $searchLocale
                ));
        $form = $formBuilder->getForm();

        $templateParam = array(
            'form'          => $form->createView(),
            'search'        => urlencode($searchPhrase),
            'searchPhrase'  => $searchPhrase,
            'locale'        => $searchLocale,
            'resource'      => $searchResource,
        );

        if (isset($paginationBar)) {
            $templateParam['paginationbar'] = $paginationBar;
        }

        if (isset($searchResults)) {
            $templateParam['searchResults'] = $searchResults;
        }

        return $this->render('opensixtBikiniTranslateBundle:Translate:changetext.html.twig',
            $templateParam
            );
    }

    /**
     * Returns array of locales for logged user
     *
     * @return array
     */
    private function getUserLocales()
    {
        $userdata = $this->get('security.context')->getToken()->getUser();
        $locales = $userdata->getUserLanguages();

        foreach ($locales as $locale) {
            $userLang[$locale->getId()] = $locale->getLocale();
        }

        return $userLang;
    }

    /**
     * Returns array of available resources for logged user
     */
    private function getUserResources()
    {
        $result = array();
        $userdata = $this->get('security.context')->getToken()->getUser();
        $groups = $userdata->getUserGroups()->toArray();
        foreach ($groups as $grp) {
            $resources = $grp->getResources();
            foreach ($resources as $res) {
                $result[$res->getId()] = $res->getName();
            }
        }

        return $result;
    }

    /**
     * Retrieves a field value from Request by fieldname
     *
     * @param string $fieldName
     * @return mixed
     */
    private function getFieldFromRequest($fieldName)
    {
        $request = $this->getRequest();

        $fieldValue = '';
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->get('form'); // form fields
            if (!empty($formData[$fieldName])) {
                $fieldValue = $formData[$fieldName];
            }
        } elseif ($request->getMethod() == 'GET') {
            if ($request->query->get($fieldName)) {
                $fieldValue = urldecode($request->query->get($fieldName));
            }
        }

        return $fieldValue;
    }

}
