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
        $admin->setIsactive(1);
        $admin->addUserRole($manager->merge($this->getReference('role-1')));
        $manager->persist($admin);

        $user = new User;
        $user->setUsername('user');
        $user->setPassword('user');
        $user->setEmail('user@bikinitranslate');
        $user->setIsactive(1);
        $user->addUserRole($manager->merge($this->getReference('role-3')));
        $manager->persist($user);
        
        $manager->flush();
    }
    
    public function getOrder() {
        return 2;
    }
}
