<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use opensixt\BikiniTranslateBundle\Helpers\Pagination;


class TranslateController extends Controller
{

    /**
     * Pagination limit
     * @var int
     */
    private $_paginationLimit;

    public function __construct() {
        $this->_paginationLimit = 20;
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

        /*$queryParameters = array(
            '' =>
        );
        $tr->setQueryParameters();*/

        $texts = $tr->getMissingTranslations('', 3, 2, 4);
//print_r ($texts);
        return $this->render('opensixtBikiniTranslateBundle:Translate:edittext.html.twig',
            array(
                'texts' => $texts,
            ));
    }

    /**
     * Returns array of locales for loged user
     *
     * @return array
     */
    private function getLocaleForLoggedUser()
    {
       $userdata = $this->get('security.context')->getToken()->getUser();
       $locale = $userdata->getUserLanguages()->toArray();
       return $locale;
    }

}
