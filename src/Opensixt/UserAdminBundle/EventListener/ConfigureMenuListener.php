<?php

namespace Opensixt\UserAdminBundle\EventListener;

use Opensixt\BikiniTranslateBundle\Menu\Event\Configure as ConfigureMenuEvent;

class ConfigureMenuListener
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public $translator;

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        if ($this->securityContext->isGranted('ROLE_USER')) {
            $menu = $event->getMenu();

            $menu = $menu->addChild($this->translator->trans('menu.administration'))
                ->setAttribute('dropdown', true);

            $menu->addChild($this->translator->trans('menu.users'), array('route' => '_admin_userlist'));

            if ($this->securityContext->isGranted('ROLE_ADMIN')) {

                $menu->addChild($this->translator->trans('menu.groups'), array('route' => '_admin_grouplist'));
                $menu->addChild($this->translator->trans('menu.languages'), array('route' => '_admin_langlist'));
                $menu->addChild($this->translator->trans('menu.resources'), array('route' => '_admin_reslist'));
            }
        }
    }
}
