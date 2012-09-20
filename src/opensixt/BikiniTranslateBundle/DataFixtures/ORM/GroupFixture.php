<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Uwe Pries <uwe.pries@sixt.com>
 * @author Paul Seiffert <paul.seiffert@mayflower.de>
 */
class GroupFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $dummygroup = new Group;
        $dummygroup->setName('Default');
        $dummygroup->setDescription('Just a default group');
        $dummygroup->setResources(array($manager->merge($this->getReference('res-default'))));

        $manager->persist($dummygroup);

        $admingroup = new Group;
        $admingroup->setName('Admingroup');
        $admingroup->setDescription('Admingroup for translating the tool');
        $admingroup->setResources(array($manager->merge($this->getReference('res-admin'))));

        $manager->persist($admingroup);
        $manager->flush();

        $this->addReference('groups-default', $dummygroup);
        $this->addReference('groups-admin', $admingroup);

        $this->getGroupAclHelper()->initAclForNewGroup($dummygroup);
        $this->getGroupAclHelper()->initAclForNewGroup($admingroup);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * @return \opensixt\BikiniTranslateBundle\AclHelper\Group
     */
    public function getGroupAclHelper()
    {
        return $this->container->get('opensixt.bikini_translate.acl_helper.group');
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

