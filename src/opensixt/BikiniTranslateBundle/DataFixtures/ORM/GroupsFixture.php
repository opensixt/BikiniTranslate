<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\Groups;

/**
 * @author uwe.pries@sixt.com
 */
class GroupsFixture extends AbstractFixture implements OrderedFixtureInterface {
    public function load(ObjectManager $manager) {
        $dummy = new Groups;
        $dummy->setName('Dummygroup');
        $dummy->setDescription('Just a dummy group');
        $dummy->setResources(array($manager->merge($this->getReference('res-dummy'))));

        $manager->persist($dummy);
        $manager->flush();

        $this->addReference('groups-dummy', $dummy);
    }

    public function getOrder() {
        return 4;
    }
}
