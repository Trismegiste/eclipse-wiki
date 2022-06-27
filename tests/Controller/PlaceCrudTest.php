<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Morph;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $npc = new Transhuman('Wizard', new Background('back'), new Faction('fact'));
        $npc->setMorph(new Morph('morph'));
        $repo->save($npc);
        
        return $npc->getPk();
    }

    /** @depends testClean */
    public function testCreate(\MongoDB\BSON\ObjectIdInterface $pkNpc)
    {
        $crawler = $this->client->request('GET', '/place/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('place_create')->form();
        $form->setValues(['place' => [
                'title' => 'Tatooine',
                'content' => 'Some link to [[Luke]]',
                'npcTemplate' => (string) $pkNpc
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
        $this->assertPageTitleContains('Wizard');
        $avatar = $crawler->filter('section.quick-npc figure');
        $this->assertCount(48, $avatar);
        $firstName = $avatar->first()->attr('data-avatar');
        $this->assertMatchesRegularExpression('#[A-Z][a-z]+\s+[A-Z][a-z]+#', $firstName);

        return $firstName;
    }

    public function testCreateNotFoundTemplate()
    {
        $this->client->request('GET', '/place/wildcard/John/Unknown');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateWildcard()
    {
        $template = new Transhuman('Warrior', new Background('dummy'), new Faction('dummy'));
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->save($template);

        $this->client->request('GET', '/place/wildcard/John/Warrior');
        $this->assertResponseRedirects();
        $this->assertStringStartsWith('/npc/edit/', $this->client->getResponse()->headers->get('Location'));
    }

}
