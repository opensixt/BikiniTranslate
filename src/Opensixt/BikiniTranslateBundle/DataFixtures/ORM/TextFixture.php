<?php

namespace Opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opensixt\BikiniTranslateBundle\Entity\Text;

/**
 * @author uwe.pries@sixt.com
 */
class TextFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // first text
        $text = new Text;
        $text->setSource('Hallo Welt');
        $text->setResource($manager->merge($this->getReference('res-default')));
        $text->setLocale($manager->merge($this->getReference('language-en_GB')));
        $text->setUser($manager->merge($this->getReference('users-user')));
        $text->setTarget('Hello world');

        $manager->persist($text);
        $manager->flush();

        // second text
        $text2 = new Text;
        $text2->setSource('Hallo Welt2');
        $text2->setResource($manager->merge($this->getReference('res-default')));
        $text2->setLocale($manager->merge($this->getReference('language-en_GB')));
        $text2->setUser($manager->merge($this->getReference('users-user')));
        $text2->setTarget('Hello world2');

        $manager->persist($text2);
        $manager->flush();

        // second text, rev 2
        $text2->setTarget('Hello World2');

        $manager->persist($text2);
        $manager->flush();

        // second text, rev 3
        $text2->setTarget('Hello World2!!!');

        $manager->persist($text2);
        $manager->flush();
    }

    public function getOrder()
    {
        return 6;
    }
}
