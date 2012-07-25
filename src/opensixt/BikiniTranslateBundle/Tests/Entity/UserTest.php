<?php

namespace opensixt\BikiniTranslateBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use opensixt\BikiniTranslateBundle\Entity\User;

class UserTest extends WebTestCase {

    private $user_id;

    public function setUp() {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get('doctrine')->getEntityManager();
    }

    public function testCreateUser() {
        $user = new User;
        $user->setUsername('phpunit');
        $user->setPassword('phpunit');
        $user->setEmail('phpunit@bikinitranslate');
        $user->setIsactive(1);
        $this->em->persist($user);
        $this->em->flush();

        $this->user_id = $user->getId();

        $this->assertGreaterThan(0, $this->user_id);
    }

    public function tearDown() {
        // TODO: delete created user
    }

}
