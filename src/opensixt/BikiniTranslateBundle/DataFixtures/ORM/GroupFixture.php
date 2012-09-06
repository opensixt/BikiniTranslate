<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\Group;

/**
 * @author uwe.pries@sixt.com
 */
class GroupFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $dummygroup = new Group;
        $dummygroup->setName('Default');
        $dummygroup->setDescription('Just a default group');
        $dummygroup->setResources(array($manager->merge($this->getReference('res-default'))));

        $manager->persist($dummygroup);
        $manager->flush();

        $this->addReference('groups-default', $dummygroup);

        $admingroup = new Group;
        $admingroup->setName('Admingroup');
        $admingroup->setDescription('Admingroup for translating the tool');
        $admingroup->setResources(array($manager->merge($this->getReference('res-admin'))));

        $manager->persist($admingroup);
        $manager->flush();

        $this->addReference('groups-admin', $admingroup);
    }

    public function getOrder()
    {
        return 4;
    }
}

