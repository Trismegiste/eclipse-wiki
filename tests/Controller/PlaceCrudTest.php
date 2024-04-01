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
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaceCrudTest extends WebTestCase
{

    protected KernelBrowser $client;

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
        $this->assertSelectorExists('#place_create');
        $this->assertFormValue('form[name="place"]', 'place[title]', 'Lieu enfant dans Tatooine');
        $form = $crawler->selectButton('place[create]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    /** @depends testShow */
    public function testAppendMorphBank(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $url = $crawler->filterXPath('//div[@class="minitoolbar"]//i[@class="icon-cryo-morph"]/parent::a')->attr('href');
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Tatooine');
        $form = $crawler->selectButton('place_append_morph_bank_append')->form();

        $values = $form->getPhpValues();
        $values['place_append_morph_bank']['inventory'][0]['morph'] = 'DummyMorph';
        $values['place_append_morph_bank']['inventory'][0]['stock'] = 10;
        $values['place_append_morph_bank']['inventory'][0]['scarcity'] = 4;

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.parsed-wikitext', 'Banque de morphes');
        $this->assertSelectorTextContains('.parsed-wikitext', 'Dispo');
        $this->assertSelectorTextContains('.parsed-wikitext', 'Stock');
        $this->assertSelectorTextContains('.parsed-wikitext', 'DummyMorph');
    }

    /** @depends testList */
    public function testPushPdf(string $show)
    {
        $crawler = $this->client->request('GET', $show);
        $push = $crawler->filterXPath('//main//div[@class="parsed-wikitext"]/table//i[@class="icon-push"]')->attr('data-title');
        $this->assertEquals('Banque de morphes', $push);
        $pk = $this->client->getRequest()->attributes->get('pk');

        $this->client->jsonRequest('POST', '/place/push-morph-bank/' . $pk, ['title' => $push]);
        $resp = $this->client->getResponse()->getContent();
        $this->assertJson($resp);
        $resp = json_decode($resp);
        $this->assertEquals('success', $resp->level);
    }

}
