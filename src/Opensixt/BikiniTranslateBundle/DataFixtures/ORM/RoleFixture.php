<?php

namespace Opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opensixt\BikiniTranslateBundle\Entity\Role;

/**
 * @author uwe.pries@sixt.com
 */
class RoleFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $super_admin = new Role('Super Admin');
        $super_admin->setName('Super Admin');
        $super_admin->setLabel('ROLE_SUPER_ADMIN');
        $manager->persist($super_admin);

        $admin = new Role('Admin');
        $admin->setName('Admin');
        $admin->setLabel('ROLE_ADMIN');
        $manager->persist($admin);

        $user = new Role('User');
        $user->setName('User');
        $user->setLabel('ROLE_USER');
        $manager->persist($user);

        $guest = new Role('Guest');
        $guest->setName('Guest');
        $guest->setLabel('ROLE_GUEST');
        $manager->persist($guest);

        $manager->flush();

        $this->addReference('role-1', $super_admin);
        $this->addReference('role-2', $admin);
        $this->addReference('role-3', $user);
        $this->addReference('role-4', $guest);
    }

    public function getOrder()
    {
        return 1;
    }
}
