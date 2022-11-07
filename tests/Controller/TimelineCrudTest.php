<?php

/*
 * eclipse-wiki
 */

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimelineCrudTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/timeline/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('timeline_create_create')->form();
        $form->setValues(['timeline_create' => [
                'title' => 'A new hope',
                'scene' => ['Star destroyer']
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
        $this->assertCount(1, $crawler->selectButton('vertex_create'));
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

}
