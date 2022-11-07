<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SceneCrudTest extends WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testClean()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/scene/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('scene_create_create')->form();
        $form->setValues(['scene_create' => [
                'title' => 'Scene1',
                'place' => 'Stage'
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testCreateWithTitle()
    {
        $crawler = $this->client->request('GET', '/scene/create?title=baston');
        $form = $crawler->selectButton('scene_create_create')->form();
        $this->assertEquals('Baston', $form['scene_create']['title']->getValue());
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/vertex/filter');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testList */
    public function testShow(string $show)
    {
        $crawler = $this->client->request('GET', $show);
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Scene1');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Scene1');
        $this->assertCount(1, $crawler->selectButton('vertex_create'));
    }

}
