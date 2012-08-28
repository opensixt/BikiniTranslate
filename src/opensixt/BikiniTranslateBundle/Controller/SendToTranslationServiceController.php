<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use opensixt\BikiniTranslateBundle\Form\SendToTSForm;

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
     * @var \opensixt\BikiniTranslateBundle\Services\SearchString
     */
    public $searcher;

    /** @var \opensixt\BikiniTranslateBundle\Helpers\BikiniExport */
    public $exporter;

    /** @var \opensixt\BikiniTranslateBundle\Acl\UserPermissions */
    public $userPermissions;

    /**
     * sendtots Send to translation service
     *
     * @param string $locale
     */
    public function sendtotsAction($locale)
    {
        // if $locale is not set, redirect to setlocale action
        if (!$locale || $locale == 'empty') {
            // store an attribute for reuse during a later user request
            $this->session->set('targetRoute', '_translate_sendtots');
            return $this->redirect($this->generateUrl('_translate_setlocale'));
        } else {
            // get language id with locale
            $userLang = array_flip($this->getUserLocales());
            $languageId = isset($userLang[$locale]) ? $userLang[$locale] : 0;
        }
        if (!$languageId) {
            $this->session->set('targetRoute', '_translate_sendtots');
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
            'opensixtBikiniTranslateBundle:Translate:sendtots.html.twig',
            $templateParam
        );
    }
}

