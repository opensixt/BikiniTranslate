<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\Resource;

/**
 * @author uwe.pries@sixt.com
 */
class ResourceFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $res = new Resource;
        $res->setName('Default');
        $res->setDescription('Just a default resource');
        $manager->persist($res);

        $manager->flush();

        $this->addReference('res-default', $res);

        $adminres = new Resource;
        $adminres->setName('Adminres');
        $adminres->setDescription('Admin resource for the tool');
        $manager->persist($adminres);

        $manager->flush();

        $this->addReference('res-admin', $adminres);
    }

    public function getOrder()
    {
        return 3;
    }
}

