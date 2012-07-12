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


    public function __construct() {
        $this->_paginationLimit = 5;
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
     * @param type $locale
     * @param type $resources
     * @param type $page
     * @return Response A Response instance
     */
    public function edittextAction($locale, $resources = 0, $page = 1)
    {
        if (!$locale || $locale == 'empty') {
            $session = $this->get('session');
            // store an attribute for reuse during a later user request
            $session->set('targetRoute', '_translate_edittext');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $tr = $em->getRepository('opensixtBikiniTranslateBundle:Text');

        if (!$resources) {
            $resources = $this->getUserResources(); // available resources
        }


        $textCount = $tr->getTextCount(
            TextRepository::MISSING_TRANS_BY_LANG,
            $resources);

        $pagination = new Pagination($textCount, $this->_paginationLimit, $page);
        $paginationBar = $pagination->getPaginationBar();

        $texts = $tr->getMissingTranslations(
            3,
            $this->_paginationLimit,
            $pagination->getOffset());

        return $this->render('opensixtBikiniTranslateBundle:Translate:edittext.html.twig',
            array(
                'texts' => $texts,
                'paginationbar' => $paginationBar,
                'locale' => $locale,
            ));
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
                array('locale' => $locales[0]->getLocale())
                ));
        }

        $request = $this->getRequest();
        $translator = $this->get('translator');

        $data = array();
        foreach ($locales as $locale) {
            $data[$locale->getId()] = $locale->getLocale();
        }

        $form = $this->createFormBuilder()
            ->add('locale', 'choice', array(
                    'label'     => $translator->trans('please_choose_locale') . ': ',
                    'empty_value' => '',
                    'choices'   => $data,
                ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bindRequest($request);

            if ($form->isValid()) {

                if ($form->get('locale')->getData()) {
                    //echo $form->get('locale')->getData();
                    $localeId = $form->get('locale')->getData();
                    $locale = isset($data[$localeId]) ? $data[$localeId] : '';

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
                /*'texts' => $texts,
                'paginationbar' => $paginationBar,
                'locale' => $locale,*/
            ));
    }

    /**
     * Returns array of locales for logged user
     *
     * @return array
     */
    private function getUserLocales()
    {
        $userdata = $this->get('security.context')->getToken()->getUser();
        $locales = $userdata->getUserLanguages()->toArray();
        return $locales;
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
                $result[] = $res->getId();
            }
        }

        return $result;
    }

}
