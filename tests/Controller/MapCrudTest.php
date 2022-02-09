<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MapCrudTest extends WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateForm()
    {
        $crawler = $this->client->request('GET', '/map/oneblock/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('.map-generator form')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testMapGenerate()
    {
        $crawler = $this->client->request('GET', '/map/oneblock/create');
        $form = $crawler->filter('.map-generator form')->form();
        $param = $form->getValues();
        $url = '/map/oneblock/generate?' . http_build_query($param);
        ob_start();
        $this->client->request('GET', $url);
        $output = ob_get_clean();
        $this->assertEquals('image/svg+xml', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertStringStartsWith('<svg', $output);
        $this->assertStringEndsWith('</svg>', $output);
    }

    public function testBadFormGenerate()
    {
        $this->client->request('GET', '/map/oneblock/generate?yolo');
        $this->assertResponseStatusCodeSame(500);
    }

}
