<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DumpTest extends WebTestCase
{

    public function testDumpEdge(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/dump/edge');

        $this->assertResponseIsSuccessful();
    }

    public function testDumpSkill(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/dump/skill');

        $this->assertResponseIsSuccessful();
    }

    public function testDumpHindrance(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/dump/hindrance');

        $this->assertResponseIsSuccessful();
    }

}
