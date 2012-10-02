<?php

namespace Opensixt\BikiniTranslateBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;

class MenuBuilder
{
    /** @var FactoryInterface */
    public $factory;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public $translator;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcher */
    public $eventDispatcher;

    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    /**
     * @param Request $request
     * @return ItemInterface
     */
    public function createSidebarMenu(Request $request)
    {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('id', 'nav');

        if ($this->securityContext->isGranted('ROLE_USER')) {
            //$menu->setCurrentUri($request->getRequestUri());

            $this->addTranslationMenu($menu);
        }

        $this->dispatchMenuConfigureEvent($menu);

        return $menu;
    }

    /**
     * @param ItemInterface $menu
     */
    private function addTranslationMenu(ItemInterface $menu)
    {
        $translationNode = $menu->addChild($this->translator->trans('menu.translation'))
            ->setAttribute('dropdown', true);

        $translationNode->addChild(
            $this->translator->trans('menu.translation.release_text'),
            array('route' => '_translate_releasetext')
        );

        $translationNode->addChild(
            $this->translator->trans('menu.translation.edit_text'),
            array('route' => '_translate_edittext')
        );

        $translationNode->addChild(
            $this->translator->trans('menu.translation.search_string'),
            array('route' => '_translate_searchstring')
        );

        $translationNode->addChild(
            $this->translator->trans('menu.translation.change_text'),
            array('route' => '_translate_changetext')
        );

        $translationNode->addChild(
            $this->translator->trans('menu.translation.clean_text'),
            array('route' => '_translate_cleantext')
        );

        $translationNode->addChild(
            $this->translator->trans('menu.translation.copy_language'),
            array('route' => '_translate_copylanguage')
        );

        $translationNode->addChild(
            $this->translator->trans('menu.translation.copy_resource'),
            array('route' => '_translate_copyresource')
        );
    }

    /**
     * @param ItemInterface $menu
     */
    private function dispatchMenuConfigureEvent(ItemInterface $menu)
    {
        $this->eventDispatcher->dispatch(
            Events::CONFIGURE,
            new Event\Configure($this->factory, $menu)
        );
    }
}
