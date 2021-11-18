<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VertexCrudTest extends WebTestCase
{

    public function testClean()
    {
        $client = static::createClient();
        $repo = static::getContainer()->get('app.vertex.repository');
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('vertex_create')->form();
        $form->setValues(['vertex' => [
                'title' => 'A title',
                'content' => 'Some [[link1234]]'
        ]]);
        $crawler = $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
    }

    public function testCreateWithTitle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/create?title=yolo');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('vertex_create')->form();
        $this->assertEquals('Yolo', $form['vertex']['title']->getValue());
    }

    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vertex/list');
        $this->assertResponseIsSuccessful();
    }

    public function testShow()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/list');
        $url = $crawler->filterXPath('//td/nav/a/i[@class="icon-eye"]/parent::a')->attr('href');
        $crawler = $client->request('GET', $url);
        $this->assertPageTitleContains('A title');
    }

    public function testEdit()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/list');
        $url = $crawler->filterXPath('//td/nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('form_edit')->form();
        $form['form[content]'] = 'New content [[abcd]]';
        $crawler = $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
    }

    public function testSearch()
    {
        $client = static::createClient();
        $client->request('GET', '/vertex/search?q=A');
        $choice = json_decode($client->getResponse()->getContent());
        $this->assertCount(1, $choice);
        $this->assertEquals('A title', $choice[0]);
    }

    public function testShowByTitle()
    {
        $client = static::createClient();
        $client->request('GET', '/wiki/A title');
        $this->assertResponseIsSuccessful();
    }

    public function testShowNewDocument()
    {
        $client = static::createClient();
        $client->request('GET', '/wiki/Unknown');
        $this->assertResponseRedirects();
    }

    public function testDelete()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/list');
        $url = $crawler->filterXPath('//td/nav/a/i[@class="icon-trash-empty"]/parent::a')->attr('href');

        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('form_delete')->form();
        $crawler = $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
    }

}
