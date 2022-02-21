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
            ['district'],
            ['station'],
            ['spaceship']
        ];
    }

    /**
     * @dataProvider getRecipeKeys
     */
    public function testAllModelForm(string $key)
    {
        $crawler = $this->client->request('GET', "/map/create/$key");
        $this->assertResponseIsSuccessful();
    }

    public function testCreateForm()
    {
        $crawler = $this->client->request('GET', '/map/create/oneblock');

        $form = $crawler->filter('.map-generator form')->form();
        $form->setValues(['mapgen' => [
                'side' => 20,
                'iteration' => 15,
                'divide' => 1,
                'capping' => 5,
                'npc' => 0,
                'seed' => 666
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $pk = $this->client->getRequest()->attributes->get('pk');

        return $pk;
    }

    /** @depends testCreateForm */
    public function testCreatedPlace(string $pk)
    {
        $this->assertMatchesRegularExpression('#^[\da-f]{24}$#', $pk);
        $repo = static::getContainer()->get(\App\Repository\VertexRepository::class);
        $place = $repo->load($pk);
        $this->assertInstanceOf(\App\Entity\Place::class, $place);
        $this->assertNotNull($place->battleMap);
        $filename = join_paths(static::getContainer()->get(\App\Service\Storage::class)->getRootDir(), $place->battleMap);
        $this->assertFileExists($filename);
        unlink($filename);
    }

    public function testSvgGenerate()
    {
        $crawler = $this->client->request('GET', '/map/create/oneblock');
        $form = $crawler->filter('.map-generator form')->form();
        $form->setValues(['mapgen' => [
                'side' => 20,
                'iteration' => 15,
                'divide' => 1,
                'capping' => 5,
                'npc' => 0,
                'seed' => 666
        ]]);
        $param = $form->getValues();
        $url = '/map/generate/oneblock?' . http_build_query($param);
        ob_start();
        $this->client->request('GET', $url);
        $output = ob_get_clean();
        $this->assertEquals('image/svg+xml', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertStringStartsWith('<?xml', $output);
        $this->assertStringEndsWith('</svg>', $output);
    }

    public function testSvgBadForm()
    {
        $this->client->request('GET', '/map/generate/oneblock?yolo');
        $this->assertResponseStatusCodeSame(500);
    }

    public function testPopUp()
    {
        $this->client->request('GET', '/map/popup/oneblock?yolo');
        $this->assertResponseIsSuccessful();
        // since the popup only passthru query paramaters to the SVG controller (in javascript fetch)
        // we don't care to pass carefully formed parameters
    }

    public function testList()
    {
        $this->client->request('GET', '/map/list');
        $this->assertResponseIsSuccessful();
    }

}
