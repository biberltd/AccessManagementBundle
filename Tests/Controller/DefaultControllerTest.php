<?php

namespace BiberLtd\Cores\Bundles\AccessManagementBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/test/access_management_bundle');

        $this->assertTrue($crawler->filter('html:contains("Testing Access Management Bundle.")')->count() > 0);
    }
}
