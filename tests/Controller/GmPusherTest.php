<?php

/*
 * eclipse-wiki
 */

class GmPusherTest extends Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testPeering()
    {
        $this->client->request('GET', '/peering');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('QRious', $this->client->getResponse()->getContent());
    }

}
