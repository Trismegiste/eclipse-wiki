<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NpcGeneratorTest extends WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testClean()
    {
        $repo = static::getContainer()->get(\App\Repository\VertexRepository::class);
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/npc/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('npc_generate')->form();
        $form->setValues(['npc' => [
                'title' => 'Luke',
                'background' => 'Hilote',
                'faction' => 'Tamiseur',
                'morph' => 'Basique'
        ]]);
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/vertex/filter');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');

        $this->assertStringContainsString('show', $url);

        return $url;
    }

    /**
     * @depends testList
     */
    public function testShow(string $url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertPageTitleContains('Luke');
        $url = $crawler->filterXPath('//a/i[@class="icon-d6"]/parent::a')->attr('href');

        return $url;
    }

    /**
     * @depends testShow
     */
    public function testEdit(string $url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public function testSearch()
    {
        $this->client->request('GET', '/vertex/search?q=Lu');
        $listing = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(1, $listing);

        return $listing[0];
    }

    public function testAjaxBackground()
    {
        $this->client->request('GET', "/npc/background/info?key=Hilote");
        $this->assertResponseIsSuccessful();
    }

    public function testAjaxFaction()
    {
        $this->client->request('GET', "/npc/faction/info?key=Tamiseur");
        $this->assertResponseIsSuccessful();
    }

    public function testAjaxMorph()
    {
        $this->client->request('GET', "/npc/morph/info?key=Basique");
        $this->assertResponseIsSuccessful();
    }

    /**
     * @depends testShow
     */
    public function testInfo(string $url): string
    {
        $crawler = $this->client->request('GET', $url);
        $url = $crawler->filterXPath('//a/i[@class="icon-edit"]/parent::a')->attr('href');
        $crawler = $this->client->request('GET', $url);
        $this->assertNotNull($crawler->selectButton('npc_info_edit'));

        return $this->client->getRequest()->attributes->get('pk');
    }

    /** @depends testInfo */
    public function testDelete(string $pk)
    {
        $crawler = $this->client->request('GET', "/vertex/delete/$pk");
        $this->assertCount(1, $crawler->selectButton('form_delete'));
    }

    /** @depends testInfo */
    public function testBattle(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/battle/$pk");
        $this->assertCount(1, $crawler->selectButton('npc_attacks_edit'));
    }

    /** @depends testInfo */
    public function testDuplicate(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/duplicate/$pk");
        $button = $crawler->selectButton('form_copy');
        $this->assertCount(1, $button);

        $form = $button->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    /** @depends testInfo */
    public function testGear(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/gear/$pk");
        $this->assertCount(1, $crawler->selectButton('npc_gears_edit'));
    }

    public function testCreateALI()
    {
        $crawler = $this->client->request('GET', '/npc/ali');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('ali_generate')->form();
        $this->client->submit($form, ['ali' => ['title' => 'New ALI']]);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    /** @depends testInfo */
    public function testSleeve(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/sleeve/$pk");
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('form_sleeve');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testCreateFreeform()
    {
        $this->client->request('GET', '/npc/freeform');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('freeform_create_create', ['freeform_create' => [
                'title' => 'New monster',
                'type' => 'Monstre'
        ]]);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

}
