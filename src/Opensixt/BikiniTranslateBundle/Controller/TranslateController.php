<?php
namespace Opensixt\BikiniTranslateBundle\Controller;

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
        return $this->render('OpensixtBikiniTranslateBundle:Translate:index.html.twig');
    }
}
