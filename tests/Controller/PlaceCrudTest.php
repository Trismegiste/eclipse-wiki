<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaceCrudTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/place/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('place_create')->form();
        $form->setValues(['place' => [
                'title' => 'Tatooine',
                'content' => 'Some link to [[Luke]]'
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/vertex/list');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testList */
    public function testShow(string $show)
    {
        $crawler = $this->client->request('GET', $show);
        $this->assertPageTitleContains('Tatooine');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertPageTitleContains('Tatooine');
        $this->assertNotNull($crawler->selectButton('place_edit'));
    }

}
