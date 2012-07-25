<?php

namespace opensixt\UserAdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertTrue($crawler->filter('html:contains("Einloggen")')->count() > 0);
    }
}
