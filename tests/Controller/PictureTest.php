<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

class PictureTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testShow()
    {
        $this->client->request('GET', '/picture/show/notfound.jpg');
        $this->assertResponseIsSuccessful();
    }

    public function testSearch()
    {
        $this->client->request('GET', '/picture/search?q=yolo');
        $this->assertResponseIsSuccessful();
    }

    public function testSendBluetooth()
    {
        $this->client->request('GET', '/picture/send/notfound.jpg');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateProfile()
    {
        $npc = new \App\Entity\Ali('hal');
        self::getContainer()->get(\App\Repository\VertexRepository::class)->save($npc);
        $this->client->request('GET', '/profile/create/' . $npc->getPk());
        $this->assertResponseIsSuccessful();
    }

}
