<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use opensixt\BikiniTranslateBundle\Form\CopyResourceForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class CopyResourceController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString
     */
    public $searcher;

    /** @var \opensixt\BikiniTranslateBundle\Acl\UserPermissions */
    public $userPermissions;


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

            if (!empty($searchLanguage)) {
                $arrLang = array($searchLanguage);
            } else {
                $arrLang = array_keys($locales);
            }
            $this->searcher->setLocales($arrLang);

            $translationsCount = $this->searcher->copyResource(
                $res['from'],
                $res['to'],
                array_keys($resources)
            );
            $translateMade = 'done';
        }

        $form = $this->formFactory
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
}

