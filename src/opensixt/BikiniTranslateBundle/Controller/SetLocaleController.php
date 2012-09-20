<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use opensixt\BikiniTranslateBundle\Controller\AbstractController;
use opensixt\BikiniTranslateBundle\Form\SetLocaleForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class SetLocaleController extends AbstractController
{
    /** @var \opensixt\BikiniTranslateBundle\AclHelper\UserPermissions */
    public $userPermissions;

    /**
     * setlocale Action
     *
     * @return Response A Response instance
     */
    public function setlocaleAction()
    {
        $locales = $this->getUserLocales();
        if (count($locales) == 1) {
            return $this->redirect(
                $this->generateUrl(
                    $this->session->get('targetRoute') ? : '_translate_home',
                    array('locale' => $locales[0])
                )
            );
        }

        $form = $this->formFactory
            ->create(
                new SetLocaleForm(),
                null,
                array(
                    'locales' => $locales,
                )
            );

        if ($this->request->getMethod() == 'POST') {
            // the controller binds the submitted data to the form
            $form->bind($this->request);

            if ($form->isValid()) {
                if ($form->get('locale')->getData()) {

                    $localeId = $form->get('locale')->getData();
                    $locale = isset($locales[$localeId]) ? $locales[$localeId] : '';

                    return $this->redirect(
                        $this->generateUrl(
                            $this->session->get('targetRoute') ? : '_translate_home',
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
}

