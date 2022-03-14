<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlayerCastTest extends WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testView()
    {
        $this->client->request('GET', '/player/view');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('new WebSocket', $this->client->getResponse()->getContent());
    }

}
