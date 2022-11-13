<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VertexCrudTest extends WebTestCase
{

    protected $client;
    protected \App\Repository\VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(\App\Repository\VertexRepository::class);
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, iterator_to_array($this->repository->search()));
    }

    public function getVertexFqcn(): array
    {
        return [
            [\App\Entity\Timeline::class],
            [\App\Entity\Scene::class],
            [\App\Entity\Scene::class],
        ];
    }

    /** @dataProvider getVertexFqcn */
    public function testEdit(string $fqcn)
    {
        $vertex = new $fqcn('TestVertex' . rand());
        $this->repository->save($vertex);

        $crawler = $this->client->request('GET', '/vertex/edit/' . $vertex->getPk());
        $form = $crawler->selectButton('vertex_create')->form();
        $form['vertex[content]'] = 'New content [[file:abcd.png]]';
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        usleep(1000);
    }

    public function testCreateWithTitle(): void
    {
        $this->client->request('GET', '/vertex/create?title=yolo');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.icon-video');
        $this->assertSelectorExists('.icon-user-plus');
        $this->assertSelectorExists('.icon-place');
    }

    public function testList(): void
    {
        $this->client->request('GET', '/vertex/filter');
        $this->assertResponseIsSuccessful();
    }

    public function testShow()
    {
        $crawler = $this->client->request('GET', '/vertex/filter');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');
        $crawler = $this->client->request('GET', $url);
        $this->assertPageTitleContains('TestVertex');
    }

    public function testSearch()
    {
        $this->client->request('GET', '/vertex/search?q=test');
        $choice = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(3, $choice);
        $this->assertStringStartsWith('TestVertex', $choice[1]);

        return $choice[1];
    }

    /** @depends testSearch */
    public function testShowByTitle(string $title)
    {
        $crawler = $this->client->request('GET', '/wiki/' . $title);
        $this->assertResponseIsSuccessful();

        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');
        $crawler = $this->client->request('GET', $url);
        $this->assertPageTitleContains('TestVertex');
        $pk = $this->client->getRequest()->get('pk');

        return $pk;
    }

    public function testShowNewDocument()
    {
        $this->client->request('GET', '/wiki/Unknown');
        $this->assertResponseRedirects();
    }

    /** @depends testShowByTitle */
    public function testNavigationPrevious(string $pk)
    {
        $this->client->request('GET', '/vertex/previous/' . $pk);
        $this->assertPageTitleContains('TestVertex');
    }

    /** @depends testShowByTitle */
    public function testNavigationNext(string $pk)
    {
        $this->client->request('GET', '/vertex/next/' . $pk);
        $this->assertPageTitleContains('TestVertex');
    }

    /** @depends testShowByTitle */
    public function testRename(string $pk)
    {
        $crawler = $this->client->request('GET', '/vertex/show/' . $pk);
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-rename"]/parent::a')->attr('href');
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton('form_rename')->form();
        $form['form[title]'] = 'Renamed';
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertPageTitleContains('Renamed');
    }

    /** @depends testShowByTitle */
    public function testDelete(string $pk)
    {
        $crawler = $this->client->request('GET', '/vertex/delete/' . $pk);
        $form = $crawler->selectButton('form_delete')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

}
