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

    public function testLoginWithWrongCredencials()
    {
        $client = $this->createClient();
        $client->followRedirects(true);

        // request the index action
        $crawler = $client->request('GET', '/admin/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // select the login form
        $form = $crawler->selectButton('login')->form();

        // submit the form with bad credentials
        $crawler = $client->submit(
            $form,
            array(
                '_username' => 'john.doe',
                '_password' => 'wrong_password'
            )
        );

        // response should be success
        $this->assertTrue($client->getResponse()->isSuccessful());

        // we should have been redirected back to the login page because
        // invalid credentials were supplied
        $this->assertTrue($crawler->filter('title:contains("Login")')->count() > 0);
    }

    public function testLoginWithCorrectCredencials()
    {
        $client = $this->createClient();
        $client->followRedirects(true);

        // request the index action
        $crawler = $client->request('GET', '/admin/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // select the login form
        $form = $crawler->selectButton('login')->form();

        // submit the form with valid credentials
        $crawler = $client->submit(
            $form,
            array(
                '_username' => 'admin',
                '_password' => 'admin'
            )
        );

        // response should be success
        $this->assertTrue($client->getResponse()->isSuccessful());

        // check the title of the page matches the admin home page
        $this->assertTrue($crawler->filter('title:contains("Dashboard")')->count() > 0);

        // check that the logout link exists
        $this->assertTrue($crawler->filter('a:contains("Logout")')->count() > 0);
    }
}

