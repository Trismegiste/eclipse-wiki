<?php

/*
 * eclipse-wiki
 */

class PlayerLogTest extends Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testPeering()
    {
        $this->client->request('GET', '/player/peering');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('mercure', $this->client->getResponse()->getContent());
    }

    public function testLog()
    {
        $this->client->request('GET', '/player/log');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('swiper', $this->client->getResponse()->getContent());
    }

}
