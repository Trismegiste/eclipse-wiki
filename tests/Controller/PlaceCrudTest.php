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

        $npc = new \App\Entity\Transhuman('Template', new \App\Entity\Background('back'), new \App\Entity\Faction('fact'));
        $npc->setMorph(new \App\Entity\Morph('morph'));
        $repo->save($npc);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/place/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('place_create')->form();
        $form->setValues(['place' => [
                'title' => 'Tatooine',
                'content' => 'Some link to [[Luke]]',
                'npcTemplate' => 'Template'
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testCreateWithTitle()
    {
        $crawler = $this->client->request('GET', '/place/create?title=alderaan');
        $form = $crawler->selectButton('place_create')->form();
        $this->assertEquals('Alderaan', $form['place']['title']->getValue());
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
        $this->assertPageTitleContains('Tatooine');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertPageTitleContains('Tatooine');
        $this->assertCount(1, $crawler->selectButton('place_create'));
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-user-plus"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testEdit */
    public function testShowNpcGeneration(string $useradd)
    {
        $crawler = $this->client->request('GET', $useradd);
        $this->assertPageTitleContains('Tatooine');
        $avatar = $crawler->filter('section.quick-npc figure');
        $this->assertCount(48, $avatar);
        $firstName = $avatar->first()->attr('data-avatar');
        $this->assertMatchesRegularExpression('#[A-Z][a-z]+\s+[A-Z][a-z]+#', $firstName);

        return $firstName;
    }

}
