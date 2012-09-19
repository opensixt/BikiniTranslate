<?php

namespace opensixt\SxTranslateBundle\EventListener;

use opensixt\BikiniTranslateBundle\Menu\Event\Configure as ConfigureMenuEvent;

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

            $menu = $menu->addChild($this->translator->trans('menu.freetext'));

            $menu->addChild($this->translator->trans('menu.addfreetext'), array('route' => '_sxfreetext_add'));
            $menu->addChild($this->translator->trans('menu.editfreetext'), array('route' => '_sxfreetext_edit'));
            $menu->addChild($this->translator->trans('menu.statusfreetext'), array('route' => '_sxfreetext_status'));
        }
    }
}

