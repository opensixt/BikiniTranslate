<?php

namespace opensixt\BikiniTranslateBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
abstract class AbstractController
{
    /** @var \Symfony\Component\HttpFoundation\Request */
    public $request;

    /** @var \Symfony\Component\HttpFoundation\Session */
    public $session;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public $translator;

    /** @var \Doctrine\ORM\EntityManager */
    public $em;

    /** @var \Symfony\Component\Form\FormFactory */
    public $formFactory;

    /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface */
    public $templating;

    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    public $router;

    /** @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface */
    public $aclProvider;

    /** @var string */
    public $toolLanguage;

    /** @var \opensixt\BikiniTranslateBundle\Helpers\BikiniFlash */
    public $bikiniFlash;

    /**
     * @param string $url
     * @param int $status
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * @param string $route
     * @param array $parameters
     * @param bool $absolute
     * @return mixed
     */
    protected function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->router->generate($route, $parameters, $absolute);
    }

    /**
     *
     * @return type
     */
    protected function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }

    /**
     * @throws AccessDeniedException
     */
    protected function requireAdminUser()
    {
        if (!$this->isAdminUser()) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @return bool
     */
    protected function isAdminUser()
    {
        return $this->securityContext->isGranted('ROLE_ADMIN');
    }

    /**
     * Returns array of locales for logged user
     *
     * @return array
     */
    protected function getUserLocales()
    {
        $userdata = $this->securityContext->getToken()->getUser();
        $locales = $userdata->getUserLanguages();

        foreach ($locales as $locale) {
            $userLang[$locale->getId()] = $locale->getLocale();
        }

        uasort(
            $userLang,
            // @codingStandardsIgnoreStart
            function ($a, $b) {
            // @codingStandardsIgnoreEnd
                return strcmp($a, $b);
            }
        );

        return $userLang;
    }

    /**
     * Returns array of available resources for logged user
     *
     * @return array
     */
    protected function getUserResources()
    {
        $result = array();
        $userdata = $this->securityContext->getToken()->getUser();
        $groups = $userdata->getUserGroups()->toArray();
        foreach ($groups as $grp) {
            $resources = $grp->getResources();
            foreach ($resources as $res) {
                $result[$res->getId()] = $res->getName();
            }
        }

        return $result;
    }

    /**
     * Retrieves a field value from Request by fieldname
     * if it doesn't exist, return empty string
     *
     * @param string $fieldName
     * @return mixed
     */
    protected function getFieldFromRequest($fieldName)
    {
        $fieldValue = '';
        if ($this->request->getMethod() == 'POST') {
            $formData = $this->request->get('form'); // form fields
            if (!empty($formData[$fieldName])) {
                $fieldValue = $formData[$fieldName];
            } else {
                $fieldValue = $this->request->get($fieldName, '');
            }
        } elseif ($this->request->getMethod() == 'GET') {
            if ($this->request->get($fieldName)) {
                $fieldValue = urldecode($this->request->get($fieldName));
            }
        }

        return $fieldValue;
    }

    /**
     * Get search resources
     *
     * @return array
     */
    protected function getSearchResources()
    {
        // retrieve resource from request
        $searchResource = $this->getFieldFromRequest('resource');
        $resources = $this->getUserResources(); // available resources

        if (strlen($searchResource) && !empty($resources[$searchResource])) {
            // if $searchResource is set and available
            $searchResources = array($searchResource);
        } else {
            // all available resources
            $searchResources = array_keys($resources);
        }
        return $searchResources;
    }

    /**
     * Return $_REQUEST content as array
     *
     * @param Request $request
     * @return array
     */
    protected function getRequestData($request)
    {
        $requestString = $request->getContent();
        parse_str($requestString, $requestData);
        return $requestData;
    }

    /**
     * Return Texts (text_[number]) to save from $_REQUEST
     *
     * @param array $formData equals _REQUEST
     * @param string $fieldNamePrefix, 'text', 'chk', etc
     * @return array
     */
    protected function getTextsToSaveFromRequest($formData, $fieldNamePrefix)
    {
        $textsToSave = array();
        if (!empty($formData)) {
            foreach ($formData as $key => $value) {
                // for all textareas with name 'text_[number]'
                if (preg_match("/" . $fieldNamePrefix . "_([0-9]+)/", $key, $matches) && strlen($value)) {
                    $textsToSave[$matches[1]] = $value;
                }
            }
        }

        return $textsToSave;
    }

    /**
     * Get Array of Ids from $results
     *
     * @param ArrayCollection $results
     * @return array
     */
    protected function getIdsFromResults($results)
    {
        $ids = array();
        if (!empty($results)) {
            foreach ($results as $elem) {
                $ids[] = $elem->getId();
            }
        }

        return $ids;
    }
}

