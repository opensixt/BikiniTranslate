<?php

namespace Opensixt\BikiniTranslateBundle\Controller;

use Opensixt\BikiniTranslateBundle\Form\SendToTSForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class SendToTranslationServiceController extends AbstractController
{
    /**
     * intermediate layer
     *
     * @var \Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString
     */
    public $searcher;

    /** @var \Opensixt\BikiniTranslateBundle\Helpers\BikiniExport */
    public $exporter;

    /** @var \Opensixt\BikiniTranslateBundle\AclHelper\UserPermissions */
    public $userPermissions;

    /**
     * sendtots Send to translation service
     *
     * @param string $locale
     */
    public function sendtotsAction($locale)
    {
        $languageId = $this->getLanguageId($locale);
        if (!$languageId) {
            // save current ruote in session (for comeback)
            $this->session->set('targetRoute', '_translate_sendtots');
            // if $locale is not set, redirect to setlocale action
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        }

        // set search parameters
        $resources = $this->getUserResources(); // all available resources
        $this->searcher->setPaginationLimit(0); // pagination off

        // get search results
        $data = $this->searcher
            ->getData(
                1,
                $languageId,
                array_keys($resources),
                array_keys($resources)
            );

        $form = $this->formFactory
            ->create(new SendToTSForm(), null, array());

        $templateParam = array(
            'form' => $form->createView(),
            'locale' => $locale,
            'data' => $data,
        );

        // Send data to translation service
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->getRequestData($this->request);
            if ($formData && count($data)) {
                if (isset($formData['action']) && $formData['action'] == 'send') {
                    $chunks = $this->searcher->prepareExportData($data);

                    $this->exporter->setTargetLanguage($locale);
                    $this->exporter->initXliff('human_translation_service');

                    foreach ($chunks as $chunk) {
                        $exportXliff = $this->exporter->getDataAsXliff($chunk);
                        $this->searcher->sendToTranslationService($exportXliff, $chunk);
                    }
                    $templateParam['success'] = 1;
                }
            }
        }

        return $this->render(
            'OpensixtBikiniTranslateBundle:Translate:sendtots.html.twig',
            $templateParam
        );
    }
}
