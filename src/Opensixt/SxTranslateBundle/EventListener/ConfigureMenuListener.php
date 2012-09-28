<?php

namespace Opensixt\SxTranslateBundle\EventListener;

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
        // Freetexts
        if ($this->securityContext->isGranted('ROLE_USER')) {
            $menuF = $event->getMenu();

            $menuF = $menuF->addChild($this->translator->trans('menu.freetext'));

            $menuF->addChild($this->translator->trans('menu.addfreetext'), array('route' => '_sxfreetext_add'));
            $menuF->addChild($this->translator->trans('menu.editfreetext'), array('route' => '_sxfreetext_edit'));
            $menuF->addChild($this->translator->trans('menu.statusfreetext'), array('route' => '_sxfreetext_status'));
        }

        // Mobile Texts
        if ($this->securityContext->isGranted('ROLE_USER')) {
            $menuM = $event->getMenu();

            $menuM = $menuM->addChild($this->translator->trans('menu.mobile'));

            $menuM->addChild($this->translator->trans('menu.editmobile'), array('route' => '_sxmobile_edit'));
            $menuM->addChild($this->translator->trans('menu.changemobile'), array('route' => '_sxmobile_change'));
        }
    }
}
