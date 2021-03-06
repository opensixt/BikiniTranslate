<?php
namespace Opensixt\BikiniTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class AjaxResponderController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString
     */
    public $searcher;

    /**
     * savetext Action
     *
     * @return Response A Response instance
     */
    public function savetextAction()
    {
        //$resources = $this->getUserResources();
        //$locales = $this->getUserLocales();

        $text = $this->getFieldFromRequest('text');
        $id = $this->getFieldFromRequest('id');
        if (!empty($text) && !empty($id)) {
            $this->searcher->updateTexts(array($id => $text));

            $result = array(
                'status' => 0,
                'message' => 'success',
            );
        } else {
            $result = array(
                'status' => 1,
                'message' => 'failure',
            );
        }

        return new Response(json_encode($result));
    }

    /**
     * Gets json array of texts by hash
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getalltextsbyhashAction()
    {
        $hash = $this->getFieldFromRequest('hash');
        $texts = $this->searcher->getTextsByHash($hash);

        return new Response(json_encode($texts));
    }
}
