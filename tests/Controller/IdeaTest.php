<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IdeaTest extends WebTestCase
{

    public function testInspiration()
    {
        $client = static::createClient();
        $client->request('GET', '/idea');
        $this->assertResponseIsSuccessful();
    }

}
