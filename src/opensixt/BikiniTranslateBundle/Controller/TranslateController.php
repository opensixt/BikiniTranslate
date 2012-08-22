<?php
namespace opensixt\BikiniTranslateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
}

