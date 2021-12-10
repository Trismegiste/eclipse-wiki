<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EncounterCrudTest extends WebTestCase
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

        $npc = new \App\Entity\Ali('test');
        $npc->setMorph(new \App\Entity\Morph('dummy'));
        $repo->save($npc);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/encounter/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('encounter_create')->form();
        $form->setValues(['encounter' => [
                'title' => 'Astroport',
                'content' => 'Some link to [[Luke]]'
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
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
        $this->assertPageTitleContains('Astroport');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertPageTitleContains('Astroport');
        $this->assertCount(1, $crawler->selectButton('encounter_create'));

        return $this->client->getRequest()->get('pk');
    }

    /** @depends testEdit */
    public function testQrCode(string $pk)
    {
        $crawler = $this->client->request('GET', '/encounter/qrcode/' . $pk);
        $this->assertPageTitleContains('Astroport');
    }

}
