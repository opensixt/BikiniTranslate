<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\Language;

/**
 * @author uwe.pries@sixt.com
 */
class LanguageFixture extends AbstractFixture implements OrderedFixtureInterface
{
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
    }

    public function getOrder()
    {
        return 2;
    }
}

