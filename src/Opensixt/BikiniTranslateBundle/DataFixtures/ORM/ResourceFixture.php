<?php

namespace Opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opensixt\BikiniTranslateBundle\Entity\Resource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author uwe.pries@sixt.com
 */
class ResourceFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $res = new Resource;
        $res->setName('Default');
        $res->setDescription('Just a default resource');
        $manager->persist($res);

        $adminres = new Resource;
        $adminres->setName('Adminres');
        $adminres->setDescription('Admin resource for the tool');
        $manager->persist($adminres);

        $manager->flush();

        $this->addReference('res-admin', $adminres);
        $this->addReference('res-default', $res);

        $this->getResourceAclHelper()->initAclForNewResource($adminres);
        $this->getResourceAclHelper()->initAclForNewResource($res);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * @return \Opensixt\BikiniTranslateBundle\AclHelper\Resource
     */
    private function getResourceAclHelper()
    {
        return $this->container->get('opensixt.bikini_translate.acl_helper.resource');
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
