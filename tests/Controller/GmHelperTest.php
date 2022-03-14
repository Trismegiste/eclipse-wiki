<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GmHelperTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testNameGenerate()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/gm/name');
        $this->assertResponseIsSuccessful();
    }

    public function testQrCode()
    {
        $client = static::createClient();
        $client->request('GET', '/broadcast/qrcode');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('QRious', $client->getResponse()->getContent());
    }

}
