<?php

namespace Opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opensixt\BikiniTranslateBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author uwe.pries@sixt.com
 */
class UserFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var \Symfony\Component\DependencyInjection\Container */
    private $container;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $admin = new User;
        $admin->setUsername('admin');
        $admin->setPassword('admin');
        $admin->setEmail('admin@bikinitranslate');
        $admin->setIsactive(User::ACTIVE_USER);
        $admin->addUserRole($manager->merge($this->getReference('role-1')));
        $admin->setUserLanguages(
            array(
                $manager->merge($this->getReference('language-en_GB')),
                $manager->merge($this->getReference('language-de_DE')),
            )
        );
        $admin->setUserGroups(array($manager->merge($this->getReference('groups-admin'))));

        $manager->persist($admin);

        $this->addReference('users-admin', $admin);

        $user = new User;
        $user->setUsername('bikini');
        $user->setPassword('bikini');
        $user->setEmail('bikini@bikinitranslate');
        $user->setIsactive(User::ACTIVE_USER);
        $user->addUserRole($manager->merge($this->getReference('role-3')));
        $user->setUserLanguages(array($manager->merge($this->getReference('language-de_DE'))));
        $user->setUserGroups(array($manager->merge($this->getReference('groups-default'))));

        $manager->persist($user);

        $this->addReference('users-user', $user);

        $manager->flush();

        $this->getUserAclHelper()->initAclForNewUser($admin);
        $this->getUserAclHelper()->initAclForNewUser($user);
    }

    public function getOrder()
    {
        return 5;
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

    /**
     * @return \Opensixt\BikiniTranslateBundle\AclHelper\User
     */
    private function getUserAclHelper()
    {
        return $this->container->get('opensixt.bikini_translate.acl_helper.user');
    }
}
