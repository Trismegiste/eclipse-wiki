<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GmHelperTest extends WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testNameGenerate()
    {
        $this->client->request('GET', '/gm/name');
        $this->assertResponseIsSuccessful();
    }

    public function testTrackerQrcode()
    {
        $this->client->request('GET', '/tracker/qrcode');
        $this->assertResponseIsSuccessful();
    }

    public function testTrackerShow()
    {
        $this->client->request('GET', '/tracker/show');
        $this->assertResponseIsSuccessful();
    }

    public function testHelp()
    {
        $this->client->request('GET', '/help');
        $this->assertResponseIsSuccessful();
    }

    public function testGraphView3D()
    {
        $this->client->request('GET', '/digraph/view3d');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('babylon', $this->client->getResponse()->getContent());
    }

    public function testGraphJsonLoad()
    {
        $this->client->request('GET', '/digraph/load');
        $this->assertResponseIsSuccessful();
        $doc = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('vertex', $doc);
        $this->assertArrayHasKey('adjacency', $doc);
    }

    public function testAjaxName()
    {
        $this->client->request('GET', '/ajax/name?gender=male&language=chinese');
        $this->assertResponseIsSuccessful();
        $name = json_decode($this->client->getResponse()->getContent());
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
        $this->assertStringContainsString(' ', $name);
    }

}
