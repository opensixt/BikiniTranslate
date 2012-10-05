<?php

namespace Opensixt\BikiniTranslateBundle\Controller;

use Opensixt\BikiniTranslateBundle\Form\CopyLanguageForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class CopyLanguageController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString
     */
    public $searcher;

    /** @var \Opensixt\BikiniTranslateBundle\Acl\UserPermissions */
    public $userPermissions;


    /**
     * copylanguage Action
     *
     * @return Response A Response instance
     */
    public function indexAction()
    {
        $resources = $this->getUserResources(); // available resources
        $locales = $this->getUserLocales(); // available languages

        // request values
        $lang['from'] = $this->getFieldFromRequest('lang_from');
        $lang['to']   = $this->getFieldFromRequest('lang_to');

        // if set source and destination locale
        if (!empty($lang['from']) && !empty($lang['to'])
                && $lang['from'] != $lang['to']) {

            $translationsCount = $this->searcher->copyLanguage(
                $lang['from'],
                $lang['to'],
                array_keys($resources)
            );
            $translateMade = 'done';
        }

        $form = $this->formFactory
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
            'OpensixtBikiniTranslateBundle:Translate:copylanguage.html.twig',
            $templateParam
        );
    }
}
