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

    public function getRecipeKeys(): array
    {
        return [
            ['oneblock'],
            ['street'],
            ['district']
        ];
    }

    /**
     * @dataProvider getRecipeKeys
     */
    public function testAllModelForm(string $key)
    {
        $crawler = $this->client->request('GET', "/map/$key/create");
        $this->assertResponseIsSuccessful();
    }

    public function testCreateForm()
    {
        $crawler = $this->client->request('GET', '/map/oneblock/create');

        $form = $crawler->filter('.map-generator form')->form();
        $form->setValues(['mapgen' => ['side' => 20]]);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful(); // no redirection
    }

    public function testSvgGenerate()
    {
        $crawler = $this->client->request('GET', '/map/oneblock/create');
        $form = $crawler->filter('.map-generator form')->form();
        $form->setValues(['mapgen' => ['side' => 20]]);
        $param = $form->getValues();
        $url = '/map/oneblock/generate?' . http_build_query($param);
        ob_start();
        $this->client->request('GET', $url);
        $output = ob_get_clean();
        $this->assertEquals('image/svg+xml', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertStringStartsWith('<?xml', $output);
        $this->assertStringEndsWith('</svg>', $output);
    }

    public function testSvgBadForm()
    {
        $this->client->request('GET', '/map/oneblock/generate?yolo');
        $this->assertResponseStatusCodeSame(500);
    }

    public function testPopUp()
    {
        $this->client->request('GET', '/map/oneblock/popup?yolo');
        $this->assertResponseIsSuccessful();
        // since the popup only passthru query paramaters to the SVG controller (in javascript fetch)
        // we don't care to pass carefully formed parameters
    }

}
