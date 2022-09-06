<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DumpTest extends WebTestCase
{

    public function testDumpEdge(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dump/edge');
        $this->assertSelectorExists('table');

        $this->assertResponseIsSuccessful();
    }

    public function testDumpSkill(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dump/skill');
        $this->assertSelectorExists('table');

        $this->assertResponseIsSuccessful();
    }

    public function testDumpHindrance(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dump/hindrance');
        $this->assertSelectorExists('table');

        $this->assertResponseIsSuccessful();
    }

    public function testDumpGear(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dump/gear');
        $this->assertSelectorExists('table');

        $this->assertResponseIsSuccessful();
    }

    public function testDumpRanged(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dump/rw');
        $this->assertSelectorExists('table');

        $this->assertResponseIsSuccessful();
    }

}
