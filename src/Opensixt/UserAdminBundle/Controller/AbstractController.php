<?php

namespace Opensixt\UserAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Opensixt\BikiniTranslateBundle\Entity\Language;
use Opensixt\BikiniTranslateBundle\Entity\Group;
use Opensixt\BikiniTranslateBundle\Entity\Role;

abstract class AbstractController
{
    /** @var \Symfony\Component\HttpFoundation\Request */
    public $request;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public $translator;

    /** @var \Doctrine\ORM\EntityManager */
    public $em;

    /** @var \Symfony\Component\Form\FormFactory */
    public $formFactory;

    /** @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface */
    public $templating;

    /** @var \Knp\Component\Pager\Paginator */
    public $paginator;

    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    /** @var \Opensixt\BikiniTranslateBundle\Helpers\BikiniFlash */
    public $bikiniFlash;

    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    public $router;

    /** @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface */
    public $aclProvider;

    /** @var \WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs */
    public $breadcrumbs;

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
     * @throws AccessDeniedException
     */
    protected function requireAdminUser()
    {
        if (!$this->isAdminUser()) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Returns array of locales
     *
     * @return array
     */
    protected function getLocales()
    {
        $languageRepository = $this->em->getRepository(Language::ENTITY_LANGUAGE);
        $languages = $languageRepository->findAll();

        foreach ($languages as $locale) {
            $locales[$locale->getId()] = $locale->getLocale();
        }

        uasort(
            $locales,
            // @codingStandardsIgnoreStart
            function ($a, $b) {
            // @codingStandardsIgnoreEnd
                return strcmp($a, $b);
            }
        );

        return $locales;
    }

    /**
     * Returns array of groups
     *
     * @return array
     */
    protected function getGroups()
    {
        $groupRepository = $this->em->getRepository(Group::ENTITY_GROUP);
        $groups = $groupRepository->findAll();

        foreach ($groups as $group) {
            $list[$group->getId()] = strtolower($group->getName());
        }

        uasort(
            $list,
            // @codingStandardsIgnoreStart
            function ($a, $b) {
            // @codingStandardsIgnoreEnd
                return strcmp($a, $b);
            }
        );

        return $list;
    }

    /**
     * Returns array of roles
     *
     * @return array
     */
    protected function getRoles()
    {
        $roleRepository = $this->em->getRepository(Role::ENTITY_ROLE);
        $roles = $roleRepository->findAll();

        foreach ($roles as $role) {
            $list[$role->getId()] = $role->getName();
        }

        uasort(
            $list,
            // @codingStandardsIgnoreStart
            function ($a, $b) {
            // @codingStandardsIgnoreEnd
                return strcmp($a, $b);
            }
        );

        return $list;
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
     * @return bool
     */
    protected function isAdminUser()
    {
        return $this->securityContext->isGranted('ROLE_ADMIN');
    }
}
