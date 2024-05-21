<?php

/*
 * eclipse-wiki
 */

use App\Entity\Scene;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimelineCrudTest extends WebTestCase
{

    protected $client;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, iterator_to_array($this->repository->search()));
    }

    public function testCreate()
    {
        $scene = new Scene('Star destroyer');
        $scene->setContent('DV');
        $this->repository->save($scene);

        $crawler = $this->client->request('GET', '/timeline/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('timeline_create_create')->form();
        $form->setValues(['timeline_create' => [
                'title' => 'A new hope',
                'elevatorPitch' => 'In space',
                'tree' => ['[[Star destroyer]]']
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testCreateWithTitle()
    {
        $crawler = $this->client->request('GET', '/timeline/create?title=fight');
        $form = $crawler->selectButton('timeline_create_create')->form();
        $this->assertEquals('Fight', $form['timeline_create']['title']->getValue());
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
        $this->assertPageTitleContains('A new hope');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('A new hope');
        $button = $crawler->selectButton('timeline_create');
        $this->assertCount(1, $button);
        // changing tree
        $form = $button->form();
        $jsonTree = $form->get('timeline[tree]')->getValue();
        $this->assertStringStartsWith('{"data":{"title":"Root"', $jsonTree);
        $tree = json_decode($jsonTree);
        $tree->nodes[] = new App\Entity\PlotNode('Tantive IV');
        $form->get('timeline[tree]')->setValue(json_encode($tree));
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $pk = $this->client->getRequest()->get('pk');
        $this->client->request('GET', "/vertex/show/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Tantive', $this->client->getResponse()->getContent());
    }

    public function testPin()
    {
        $this->client->request('GET', '/wiki/Star destroyer');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('aside .info i.icon-movie-roll');

        $crawler = $this->client->request('GET', '/wiki/A new hope');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('aside .info i.icon-pin');
        $url = $crawler->filter('aside .info i.icon-pin')->ancestors()->attr('href');

        $this->client->request('GET', $url);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/wiki/Star destroyer');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('aside .info i.icon-movie-roll');
    }

    public function testArchive()
    {
        $crawler = $this->client->request('GET', '/wiki/A new hope');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-archive"]/parent::a')->attr('href');

        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton('form_archive')->form();
        $form['form[archived]']->tick();
        $this->client->submit($form);
    }

    /** @depends testList */
    public function testBroken(string $show)
    {
        $crawler = $this->client->request('GET', $show);
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('A new hope');
        $link = $crawler->filterXPath('//nav/a/i[@class="icon-broken-child"]/parent::a')->link();
        $this->client->click($link);
        $this->assertResponseIsSuccessful();
    }

    /** @depends testList */
    public function testAjaxPartition(string $show)
    {
        $this->client->request('GET', $show);
        $pk = $this->client->getRequest()->attributes->get('pk');
        $this->client->xmlHttpRequest('GET', "/timeline/partition/$pk");
        $this->assertStringContainsString('Star destroyer', $this->client->getResponse()->getContent());
        $this->assertResponseIsSuccessful();
    }

}
