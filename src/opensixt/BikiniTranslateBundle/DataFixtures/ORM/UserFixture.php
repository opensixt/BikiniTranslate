<?php

namespace opensixt\BikiniTranslateBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use opensixt\BikiniTranslateBundle\Entity\User;

/**
 * @author uwe.pries@sixt.com
 */
class UserFixture extends AbstractFixture implements OrderedFixtureInterface {
    public function load(ObjectManager $manager) {
        $admin = new User;
        $admin->setUsername('admin');
        $admin->setPassword('admin');
        $admin->setEmail('admin@bikinitranslate');
        $admin->setIsactive(User::ActiveUser);
        $admin->addUserRole($manager->merge($this->getReference('role-1')));
        $admin->setUserLanguages(array(
            $manager->merge($this->getReference('language-en_GB')),
            $manager->merge($this->getReference('language-de_DE')),
        ));
        $admin->setUserGroups(array($manager->merge($this->getReference('groups-dummy'))));
        $manager->persist($admin);

        $user = new User;
        $user->setUsername('user');
        $user->setPassword('user');
        $user->setEmail('user@bikinitranslate');
        $user->setIsactive(User::ActiveUser);
        $user->addUserRole($manager->merge($this->getReference('role-3')));
        $user->setUserLanguages(array($manager->merge($this->getReference('language-de_DE'))));
        $user->setUserGroups(array($manager->merge($this->getReference('groups-dummy'))));
        $manager->persist($user);

        $manager->flush();
    }

    public function getOrder() {
        return 5;
    }
}
