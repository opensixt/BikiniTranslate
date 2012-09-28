<?php

namespace Opensixt\BikiniTranslateBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Opensixt\BikiniTranslateBundle\Entity\User;

class UserTest extends WebTestCase
{
    /**
     * @var int
     */
    private $user_id;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $this->em = $container->get('doctrine')->getEntityManager();
    }

    public function testCreateUser()
    {
        $user = new User;
        $user->setUsername('phpunit');
        $user->setPassword('phpunit');
        $user->setEmail('phpunit@bikinitranslate');
        $user->setIsactive(User::ACTIVE_USER);
        $this->em->persist($user);
        $this->em->flush();

        $this->user_id = $user->getId();

        $this->assertGreaterThan(0, $this->user_id);
    }

    public function tearDown()
    {
        $user = $this->em->find('Opensixt\BikiniTranslateBundle\Entity\User', $this->user_id);
        $this->em->remove($user);
        $this->em->flush();

        parent::tearDown();
    }
}
