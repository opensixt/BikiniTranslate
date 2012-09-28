<?php

namespace Opensixt\BikiniTranslateBundle\EventListener\Kernel;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class Controller
{
    /** @var \Symfony\Component\Security\Core\SecurityContextInterface */
    public $securityContext;

    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function onController(FilterControllerEvent $event)
    {
        $route = $event->getRequest()->get('_route');

        $objectIdentity = new ObjectIdentity($route, 'route');

        if ('_login' !== $route
            && !$this->securityContext->isGranted('VIEW', $objectIdentity)) {
            throw new AccessDeniedException();
        }
    }
}
