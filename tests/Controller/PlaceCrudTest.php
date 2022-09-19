<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Morph;
use App\Repository\CharacterFactory;
use App\Repository\VertexRepository;
use MongoDB\BSON\ObjectIdInterface;
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

        // fixtures
        $fac = static::getContainer()->get(CharacterFactory::class);
        $npc = $fac->create('Wizard', new Background('back'), new Faction('fact'));
        $npc->setMorph(new Morph('morph'));
        $npc->surnameLang = 'german';
        $repo->save($npc);

        return $npc->getPk();
    }

    /** @depends testClean */
    public function testCreate(ObjectIdInterface $pkNpc)
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
    }

    /** @depends testList */
    public function testChildPlace(string $show)
    {
        $this->client->request('GET', $show);
        $pk = $this->client->getRequest()->get('pk');
        $crawler = $this->client->request('GET', "/place/child/$pk");
        $this->assertCount(1, $crawler->selectButton('place_create'));
        $this->assertFormValue('form[name="place"]', 'place[title]', 'Lieu enfant dans Tatooine');
    }

}
