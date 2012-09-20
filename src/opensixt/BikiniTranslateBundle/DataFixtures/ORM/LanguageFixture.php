<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\Language;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author uwe.pries@sixt.com
 */
class LanguageFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $en = new Language;
        $en->setLocale('en_GB');
        $en->setDescription('English (GB)');
        $manager->persist($en);

        $de = new Language;
        $de->setLocale('de_DE');
        $de->setDescription('Deutsch (DE)');
        $manager->persist($de);

        $manager->flush();

        $this->addReference('language-en_GB', $en);
        $this->addReference('language-de_DE', $de);

        $this->getLanguageAclHelper()
             ->initAclForNewLanguage($en);
        $this->getLanguageAclHelper()
             ->initAclForNewLanguage($de);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\AclHelper\Language
     */
    private function getLanguageAclHelper()
    {
        return $this->container->get('opensixt.bikini_translate.acl_helper.language');
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

