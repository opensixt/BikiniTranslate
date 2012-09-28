<?php

namespace Opensixt\BikiniTranslateBundle\Controller;

use Opensixt\BikiniTranslateBundle\Form\ReleaseTextForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class ReleaseTextController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString
     */
    public $searcher;

    /** @var \Opensixt\BikiniTranslateBundle\AclHelper\UserPermissions */
    public $userPermissions;


    /**
     * releasetext Action
     *
     * @return Response A Response instance
     */
    public function releasetextAction($page)
    {
        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();

        // retrieve request parameters
        $searchResource = $this->getFieldFromRequest('resource');
        $searchLanguage = $this->getFieldFromRequest('locale');
        $searchResources = $this->getSearchResources();

        // Update texts or new search
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }
            if (isset($formData['action']) && $formData['action'] == 'save') {
                $textsToRelease = $this->getTextsToSaveFromRequest(
                    $formData,
                    'chk'
                );
                if (count($textsToRelease)) {
                    $this->searcher->releaseTexts(array_keys($textsToRelease));
                    $this->bikiniFlash->successSave();
                    return $this->redirectAfterSave(
                        '_translate_releasetext',
                        $page
                    );
                }
            }
        }

        // set search parameters
        $this->searcher->setLocales(array_keys($locales));

        // get search results
        $results = $this->searcher->getData($page, $searchLanguage, $searchResources);

        // ids of texts
        $ids = $this->getIdsFromResults($results);

        $form = $this->formFactory
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
            'OpensixtBikiniTranslateBundle:Translate:releasetext.html.twig',
            $templateParam
        );
    }
}
