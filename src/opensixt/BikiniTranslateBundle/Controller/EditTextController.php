<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use opensixt\BikiniTranslateBundle\Form\EditTextForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class EditTextController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \opensixt\BikiniTranslateBundle\IntermediateLayer\EditText
     */
    public $editText;

    /** @var \opensixt\BikiniTranslateBundle\Acl\UserPermissions */
    public $userPermissions;

    /**
     * edittext Action
     *
     * @param string $locale
     * @param int $page
     * @return Response A Response instance
     */
    public function edittextAction($locale, $page = 1)
    {
        $languageId = $this->getLanguageId($locale);
        if (!$languageId) {
            // save current ruote in session (for comeback)
            $this->session->set('targetRoute', '_translate_edittext');
            // if $locale is not set, redirect to setlocale action
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        $currentLangIsCommonLang = $this->editText
            ->compareCommonAndCurrentLocales($languageId);

        $resources = $this->getUserResources(); // all available resources

        // Update texts with entered values
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);

            if (isset($formData['action']) && $formData['action'] == 'search') {
                $page = 1;
            }

            if (isset($formData['action']) && $formData['action'] == 'save') {
                $textsToSave = $this->getTextsToSaveFromRequest(
                    $formData,
                    'text'
                );
                if (count($textsToSave)) {
                    $this->editText->updateTexts($textsToSave);
                    $this->bikiniFlash->successSave();
                }
            }
        }

        // set search parameters
        $searchResources = $this->getSearchResources();
        $getSuggestionsFlag = true; // get translations with same hash from other resources

        // get search results
        $data = $this->editText
            ->getData(
                $page,
                $languageId,
                $searchResources,
                array_keys($resources),
                $getSuggestionsFlag
            );

        $ids = $this->getIdsFromResults($data);

        $searchResource = $this->getFieldFromRequest('resource');
        $form = $this->formFactory
            ->create(
                new EditTextForm(),
                null,
                array(
                    'searchResource' => $searchResource,
                    'resources'      => $resources,
                    'ids'            => $ids,
                )
            );

        $templateParam = array(
            'form'                    => $form->createView(),
            'texts'                   => $data,
            'locale'                  => $locale,
            'currentLangIsCommonLang' => $currentLangIsCommonLang,
        );
        if ($searchResource) {
            $templateParam['resource'] = $searchResource;
        }

        return $this->render(
            'opensixtBikiniTranslateBundle:Translate:edittext.html.twig',
            $templateParam
        );
    }
}

