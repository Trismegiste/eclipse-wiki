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

    public function testQrCode()
    {
        $this->client->request('GET', '/broadcast/qrcode');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('QRious', $this->client->getResponse()->getContent());
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

    public function testQrCode3D()
    {
        $this->client->request('GET', '/broadcast/qrcode3d');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('QRious', $this->client->getResponse()->getContent());
    }

}
