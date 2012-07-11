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

    public function indexAction()
    {
        //print_r($this->getLocaleForLogedUser());
        return $this->render('opensixtBikiniTranslateBundle:Translate:index.html.twig');
    }

    /**
     * edittext Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function edittextAction($page = 1)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tr = $em->getRepository('opensixtBikiniTranslateBundle:Text');

        $textCount = $tr->getTextCount(TextRepository::MISSING_TRANS_BY_LANG);

        $pagination = new Pagination($textCount, $this->_paginationLimit, $page);
        $paginationBar = $pagination->getPaginationBar();

        $texts = $tr->getMissingTranslations(
            '',
            3,
            $this->_paginationLimit,
            $pagination->getOffset());
//print_r ($texts);
//print_r($this->getAvailableResourcesForUser());
        return $this->render('opensixtBikiniTranslateBundle:Translate:edittext.html.twig',
            array(
                'texts' => $texts,
                'paginationbar' => $paginationBar,
            ));
    }

    /**
     * Returns array of locales for logged user
     *
     * @return array
     */
    private function getLocalesForUser()
    {
        $userdata = $this->get('security.context')->getToken()->getUser();
        $locales = $userdata->getUserLanguages()->toArray();
        return $locales;
    }

    /**
     * Returns array of available resources for logged user
     */
    private function getAvailableResourcesForUser()
    {
        $result = array();
        $userdata = $this->get('security.context')->getToken()->getUser();
        $resources = $userdata->getUserGroups()->toArray();
        foreach ($resources as $res) {
            //echo "*";
            $result[] = $res->getResources();
        }

        return $result;
    }

}
