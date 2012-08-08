<?php

namespace opensixt\BikiniTranslateBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $loginStr = static::$kernel->getContainer()
                                   ->get('translator')
                                   ->trans('login');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("' . $loginStr . '")')->count());
    }
}
