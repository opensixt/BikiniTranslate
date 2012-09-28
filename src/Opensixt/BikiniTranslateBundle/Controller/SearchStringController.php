<?php

namespace Opensixt\BikiniTranslateBundle\Controller;

use Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString;
use Opensixt\BikiniTranslateBundle\Form\SearchStringForm;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class SearchStringController extends AbstractController
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
     * searchstring Action
     *
     * @param int $page
     * @return Response A Response instance
     */
    public function searchstringAction($page = 1)
    {
        $resources = $this->getUserResources();
        $locales = $this->getUserLocales();
        $mode = array(
            SearchString::SEARCH_EXACT => $this->translator->trans('exact_match'),
            SearchString::SEARCH_LIKE  => $this->translator->trans('like'),
        );

        // retrieve request parameters
        $searchPhrase   = $this->getFieldFromRequest('search');
        $searchResource = $this->getFieldFromRequest('resource');
        $searchMode     = $this->getFieldFromRequest('mode');
        $searchLanguage = $this->getFieldFromRequest('locale');

        $searchResources = $this->getSearchResources();

        if (strlen($searchPhrase) && !empty($searchLanguage)) {
            // set search parameters
            $this->searcher->setSearchParameters($searchPhrase, $searchMode);

            // get search results
            $results = $this->searcher->getData(
                $page,
                $searchLanguage,
                $searchResources
            );
        }

        // set default search language
        $locales_flip = array_flip($locales);
        $preferredChoices = array();

        // if tool_language (default language) is set
        if (!empty($this->toolLanguage)
                && isset($locales_flip[$this->toolLanguage])) {
            $preferredChoices = array($locales_flip[$this->toolLanguage]);
        }

        $form = $this->formFactory
            ->create(
                new SearchStringForm(),
                null,
                array(
                    'searchPhrase'     => $searchPhrase,
                    'searchResource'   => $searchResource,
                    'resources'        => $resources,
                    'searchLanguage'   => $searchLanguage,
                    'locales'          => $locales,
                    'preferredChoices' => $preferredChoices,
                    'searchMode'       => $searchMode,
                    'mode'             => $mode,
                )
            );

        $templateParam = array(
            'form'          => $form->createView(),
            'search'        => urlencode($searchPhrase),
            'searchPhrase'  => $searchPhrase,
            'mode'          => $searchMode,
            'resource'      => $searchResource,
            'locale'        => $searchLanguage,
        );
        if (isset($results)) {
            $templateParam['searchResults'] = $results;
        }

        return $this->render(
            'OpensixtBikiniTranslateBundle:Translate:searchstring.html.twig',
            $templateParam
        );
    }
}
