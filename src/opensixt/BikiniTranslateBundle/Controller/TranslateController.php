<?php
namespace opensixt\BikiniTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use opensixt\BikiniTranslateBundle\Services\SearchString;
use opensixt\BikiniTranslateBundle\Form\SearchStringForm;
use opensixt\BikiniTranslateBundle\Form\ReleaseTextForm;
use opensixt\BikiniTranslateBundle\Form\ChangeTextForm;
use opensixt\BikiniTranslateBundle\Form\SetLocaleForm;
use opensixt\BikiniTranslateBundle\Form\EditTextForm;
use opensixt\BikiniTranslateBundle\Form\CopyResourceForm;
use opensixt\BikiniTranslateBundle\Form\CopyLanguageForm;
use opensixt\BikiniTranslateBundle\Form\CleanTextForm;
use opensixt\BikiniTranslateBundle\Form\SendToTSForm;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class TranslateController extends Controller
{
    /**
     * translate index Action
     *
     * @return Response A Response instance
     */
    public function indexAction()
    {
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

        $editText = $this->get('opensixt_edittext'); // controller intermediate layer
        $currentLangIsCommonLang = $editText->compareCommonAndCurrentLocales($languageId);

        $resources = $this->getUserResources(); // all available resources

        // Update texts with entered values
        if ($request->getMethod() == 'POST') {
            $formData = $this->getRequestData($request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }

            if (isset($formData['action']) && $formData['action'] == 'save') {
                $editText->updateTexts(
                    $this->getTextsToSaveFromRequest($formData, 'text')
                );
            }
        }

        // set search parameters
        $searchResources = $this->getSearchResources();
        $getSuggestionsFlag = true; // get translations with same hash from other resources

        // get search results
        $data = $editText->getData(
            $page,
            $languageId,
            $searchResources,
            array_keys($resources),
            $getSuggestionsFlag
        );

        $ids = $this->getIdsFromResults($data);

        $searchResource = $this->getFieldFromRequest('resource');
        $form = $this->get('form.factory')
            ->create(
                new EditTextForm(),
                null,
                array(
                    'searchResource' => $searchResource,
                    'resources'      => $resources,
                    'ids'            => $ids,
                )
            );

        $templateParam = array(
            'form'                    => $form->createView(),
            'texts'                   => $data,
            'locale'                  => $locale,
            'currentLangIsCommonLang' => $currentLangIsCommonLang,
        );
        if ($searchResource) {
            $templateParam['resource'] = $searchResource;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:edittext.html.twig',
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
            return $this->redirect(
                $this->generateUrl(
                    $session->get('targetRoute') ? : '_translate_home',
                    array('locale' => $locales[0])
                )
            );
        }

        $request = $this->getRequest();

        $form = $this->get('form.factory')
            ->create(
                new SetLocaleForm(),
                null,
                array(
                    'locales' => $locales,
                )
            );

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bind($request);

            if ($form->isValid()) {
                if ($form->get('locale')->getData()) {
                    //echo $form->get('locale')->getData();
                    $localeId = $form->get('locale')->getData();
                    $locale = isset($locales[$localeId]) ? $locales[$localeId] : '';

                    return $this->redirect(
                        $this->generateUrl(
                            $session->get('targetRoute') ? : '_translate_home',
                            array('locale' => $locale)
                        )
                    );
                }
            } else {
                var_dump($form->getErrors());
            }
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:setlocale.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
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
            SearchString::SEARCH_EXACT => $translator->trans('exact_match'),
            SearchString::SEARCH_LIKE  => $translator->trans('like'),
        );

        // use tool_language (default language) for search
        $toolLang = $this->container->getParameter('tool_language');

        // retrieve request parameters
        $searchPhrase   = $this->getFieldFromRequest('search');
        $searchResource = $this->getFieldFromRequest('resource');
        $searchMode     = $this->getFieldFromRequest('mode');
        $searchLanguage = $this->getFieldFromRequest('locale');

        $searchResources = $this->getSearchResources();

        if (strlen($searchPhrase) && !empty($searchLanguage)) {
            $searcher = $this->get('opensixt_searchstring');

            // set search parameters
            $searcher->setSearchParameters($searchPhrase, $searchMode);

            // get search results
            $results = $searcher->getData(
                $page,
                $searchLanguage,
                $searchResources
            );
        }

        // set default search language
        $locales_flip = array_flip($locales);
        $preferredChoices = array();
        if (!empty($toolLang) && isset($locales_flip[$toolLang])) {
            $preferredChoices = array($locales_flip[$toolLang]);
        }

        $form = $this->get('form.factory')
            ->create(
                new SearchStringForm(),
                null,
                array(
                    'searchPhrase'     => $searchPhrase,
                    'searchResource'   => $searchResource,
                    'resources'        => $resources,
                    'searchLanguage'   => $searchLanguage,
                    'locales'          => $locales,
                    'preferredChoices' => $preferredChoices,
                    'searchMode'       => $searchMode,
                    'mode'             => $mode,
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'search'        => urlencode($searchPhrase),
            'searchPhrase'  => $searchPhrase,
            'mode'          => $searchMode,
            'resource'      => $searchResource,
            'locale'        => $searchLanguage,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:searchstring.html.twig',
            $templateParam
        );
    }

    /**
     * changetext Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function changetextAction($page)
    {
        $request = $this->getRequest();
        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchPhrase = $this->getFieldFromRequest('search');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchResource = $this->getFieldFromRequest('resource');

        $searcher = $this->get('opensixt_searchstring');

        // Update texts with entered values
        if ($request->getMethod() == 'POST') {
            $formData = $this->getRequestData($request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }

            if (isset($formData['action']) && $formData['action'] == 'save') {
                $searcher->updateTexts(
                    $this->getTextsToSaveFromRequest($formData, 'text')
                );
            }
        }

        if (strlen($searchPhrase)) {
            // set search parameters
            $searchResources = $this->getSearchResources();
            $searcher->setSearchParameters($searchPhrase);

            // get search results
            $results = $searcher->getData(
                $page,
                $searchLanguage,
                $searchResources
            );

            // ids of texts for textareas
            $ids = $this->getIdsFromResults($results);
        }

        $form = $this->get('form.factory')
            ->create(
                new ChangeTextForm(),
                null,
                array(
                    'searchPhrase'    => $searchPhrase,
                    'searchResource'  => $searchResource,
                    'resources'       => $resources,
                    'searchLanguage'  => $searchLanguage,
                    'locales'         => $locales,
                    'ids'             => !empty($ids) ? $ids : array(),
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'search'        => urlencode($searchPhrase),
            'searchPhrase'  => $searchPhrase,
            'locale'        => $searchLanguage,
            'resource'      => $searchResource,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:changetext.html.twig',
            $templateParam
        );
    }

    /**
     * cleantext Action
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return Response A Response instance
     */
    public function cleantextAction($page)
    {
        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchResource = $this->getFieldFromRequest('resource');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchResources = $this->getSearchResources();

        $searcher = $this->get('opensixt_flaggedtext');

        // set search parameters
        $searcher->setLocales(array_keys($locales));

        // get search results
        $results = $searcher->getData(
            $page,
            $searchLanguage,
            $searchResources,
            date("Y-m-d")
        );

        $form = $this->get('form.factory')
            ->create(
                new CleanTextForm(),
                null,
                array(
                    'searchResource'   => $searchResource,
                    'resources'        => $resources,
                    'searchLanguage'   => $searchLanguage,
                    'locales'          => $locales,
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'resource'      => $searchResource,
            'locale'        => $searchLanguage,
        );

        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:cleantext.html.twig',
            $templateParam
        );
    }

    /**
     * releasetext Action
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return Response A Response instance
     */
    public function releasetextAction($page)
    {
        $request = $this->getRequest();
        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchResource = $this->getFieldFromRequest('resource');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchResources = $this->getSearchResources();

        $searcher = $this->get('opensixt_flaggedtext');

        // Update texts with entered values
        if ($request->getMethod() == 'POST') {
            $formData = $this->getRequestData($request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }
            if (isset($formData['action']) && $formData['action'] == 'save') {
                $textsToRelease = $this->getTextsToSaveFromRequest(
                    $formData,
                    'chk'
                );
                if (count($textsToRelease)) {
                    $searcher->releaseTexts(array_keys($textsToRelease));
                }
            }
        }

        // set search parameters
        $searcher->setLocales(array_keys($locales));

        // get search results
        $results = $searcher->getData($page, $searchLanguage, $searchResources);

        // ids of texts
        $ids = $this->getIdsFromResults($results);

        $form = $this->get('form.factory')
            ->create(
                new ReleaseTextForm(),
                null,
                array(
                    'searchResource' => $searchResource,
                    'resources'      => $resources,
                    'searchLanguage' => $searchLanguage,
                    'locales'        => $locales,
                    'ids'            => $ids,
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'resource'      => $searchResource,
            'locale'        => $searchLanguage,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:releasetext.html.twig',
            $templateParam
        );
    }

    /**
     * copylanguage Action
     *
     * @return Response A Response instance
     */
    public function copylanguageAction()
    {
        $resources = $this->getUserResources(); // available resources
        $locales = $this->getUserLocales(); // available languages

        // request values
        $lang['from'] = $this->getFieldFromRequest('lang_from');
        $lang['to']   = $this->getFieldFromRequest('lang_to');

        // if set source and destination locale
        if (!empty($lang['from']) && !empty($lang['to'])
                && $lang['from'] != $lang['to']) {

            $copyLang = $this->get('opensixt_copydomain');

            $translationsCount = $copyLang->copyLanguage(
                $lang['from'],
                $lang['to'],
                array_keys($resources)
            );
            $translateMade = 'done';
        }

        $form = $this->get('form.factory')
            ->create(
                new CopyLanguageForm(),
                null,
                array(
                    'from'           => $lang['from'],
                    'to'             => $lang['to'],
                    'locales'        => $locales,
                )
            );

        $templateParam = array(
            'form' => $form->createView(),
        );
        if (!empty($translationsCount)) {
            $templateParam['translationsCount'] = $translationsCount;
        }
        if (!empty($translateMade)) {
            $templateParam['translateMade'] = $translateMade;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:copylanguage.html.twig',
            $templateParam
        );
    }

    /**
     * copyresource Action
     *
     * @return Response A Response instance
     */
    public function copyresourceAction()
    {
        $resources = $this->getUserResources(); // available resources
        $locales = $this->getUserLocales(); // available languages

        // request values
        $res['from']    = $this->getFieldFromRequest('res_from');
        $res['to']      = $this->getFieldFromRequest('res_to');
        $searchLanguage = $this->getFieldFromRequest('locale');

        if (!empty($res['from']) && !empty($res['to'])
                && $res['from'] != $res['to']) {
            // if set source and destination locale

            $copyRes = $this->get('opensixt_copydomain');

            if (!empty($searchLanguage)) {
                $arrLang = array($searchLanguage);
            } else {
                $arrLang = array_keys($locales);
            }
            $copyRes->setLocales($arrLang);

            $translationsCount = $copyRes->copyResource(
                $res['from'],
                $res['to'],
                array_keys($resources)
            );
            $translateMade = 'done';
        }

        $form = $this->get('form.factory')
            ->create(
                new CopyResourceForm(),
                null,
                array(
                    'from'           => $res['from'],
                    'to'             => $res['to'],
                    'resources'      => $resources,
                    'searchLanguage' => $searchLanguage,
                    'locales'        => $locales,
                )
            );

        $templateParam = array(
            'form' => $form->createView(),
        );
        if (!empty($translationsCount)) {
            $templateParam['translationsCount'] = $translationsCount;
        }
        if (!empty($translateMade)) {
            $templateParam['translateMade'] = $translateMade;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:copyresource.html.twig',
            $templateParam
        );
    }

    /**
     * sendtots Send to translation service
     *
     * @param string $locale
     */
    public function sendtotsAction($locale)
    {
        // if $locale is not set, redirect to setlocale action
        if (!$locale || $locale == 'empty') {
            // store an attribute for reuse during a later user request
            $session->set('targetRoute', '_translate_sendtots');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        } else {
            // get language id with locale
            $userLang = array_flip($this->getUserLocales());
            $languageId = isset($userLang[$locale]) ? $userLang[$locale] : 0;
        }
        if (!$languageId) {
            $session->set('targetRoute', '_translate_sendtots');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        $request = $this->getRequest();
        $searcher = $this->get('opensixt_edittext'); // controller intermediate layer

        // set search parameters
        $resources = $this->getUserResources(); // all available resources
        $searcher->setPaginationLimit(0);

        // get search results
        $data = $searcher->getData($languageId, array_keys($resources), array_keys($resources));

        $form = $this->get('form.factory')
            ->create(new SendToTSForm(), null, array());

        $templateParam = array(
            'form' => $form->createView(),
            'locale' => $locale,
            'data' => $data['texts'],
        );

        // Send data to translation service
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->get('form');
            if ($formData && count($data['texts'])) {
                if (isset($formData['action']) && $formData['action'] == 'send') {
                    $chunks = $searcher->prepareExportData($data['texts']);

                    $export = $this->get('bikini_export');
                    $export->setTargetLanguage($locale);
                    $export->initXliff('human_translation_service');

                    foreach ($chunks as $chunk) {
                        $exportXliff = $export->getDataAsXliff($chunk);
                        $searcher->sendToTranslationService($exportXliff, $chunk);
                    }
                    $templateParam['success'] = 1;
                }
            }
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:sendtots.html.twig',
            $templateParam
        );
    }

    /**
     * Returns array of locales for logged user
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
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
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return array
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
     * if it doesn't exist, return empty string
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param string $fieldName
     * @return mixed
     */
    private function getFieldFromRequest($fieldName)
    {
        $request = $this->getRequest();

        $fieldValue = '';
        if ($request->getMethod() == 'POST') {
            $formData = $request->get('form'); // form fields
            if (!empty($formData[$fieldName])) {
                $fieldValue = $formData[$fieldName];
            } else {
                $fieldValue = $request->get($fieldName, '');
            }
        } elseif ($request->getMethod() == 'GET') {
            if ($request->get($fieldName)) {
                $fieldValue = urldecode($request->get($fieldName));
            }
        }

        return $fieldValue;
    }

    /**
     * Get search resources
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @return array
     */
    private function getSearchResources()
    {
        // retrieve resource from request
        $searchResource = $this->getFieldFromRequest('resource');
        $resources = $this->getUserResources(); // available resources

        if (strlen($searchResource) && !empty($resources[$searchResource])) {
            // if $searchResource is set and available
            $searchResources = array($searchResource);
        } else {
            // all available resources
            $searchResources = array_keys($resources);
        }
        return $searchResources;
    }

    /**
     * Return $_REQUEST content as array
     *
     * @param Request $request
     * @return array
     */
    private function getRequestData($request)
    {
        $requestString = $request->getContent();
        parse_str($requestString, $requestData);
        return $requestData;
    }

    /**
     * Return Texts (text_[number]) to save from $_REQUEST
     *
     * @param array $formData equals _REQUEST
     * @param string $fieldNamePrefix, 'text', 'chk', etc
     * @return array
     */
    private function getTextsToSaveFromRequest($formData, $fieldNamePrefix)
    {
        $textsToSave = array();
        if (!empty($formData)) {
            foreach ($formData as $key => $value) {
                // for all textareas with name 'text_[number]'
                if (preg_match("/" . $fieldNamePrefix . "_([0-9]+)/", $key, $matches) && strlen($value)) {
                    $textsToSave[$matches[1]] = $value;
                }
            }
        }

        return $textsToSave;
    }

    /**
     * Get Array of Ids from $results
     *
     * @param ArrayCollection $results
     * @return array
     */
    private function getIdsFromResults($results)
    {
        $ids = array();
        if (!empty($results)) {
            foreach ($results as $elem) {
                $ids[] = $elem->getId();
            }
        }

        return $ids;
    }
}

