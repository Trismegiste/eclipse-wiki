<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VertexCrudTest extends WebTestCase
{

    public function testClean()
    {
        $client = static::createClient();
        $repo = static::getContainer()->get(\App\Repository\VertexRepository::class);
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
        $client->request('GET', '/vertex/filter');
        $this->assertResponseIsSuccessful();
    }

    public function testShow()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/filter');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');
        $crawler = $client->request('GET', $url);
        $this->assertPageTitleContains('A title');
    }

    public function testEdit()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/filter');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('vertex_create')->form();
        $form['vertex[content]'] = 'New content [[file:abcd.png]]';
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

    public function testTopBarMenuLink()
    {
        $client = static::createClient();
        for ($k = 0; $k < 3; $k++) {
            $crawler = $client->request('GET', '/vertex/create');
            $form = $crawler->selectButton('vertex_create')->form();
            $client->submit($form, ['vertex' => [
                    'title' => "vertex$k",
                    'content' => 'contenu ' . $k
            ]]);
            $this->assertResponseRedirects();
        }

        // navigation
        $crawler = $client->request('GET', '/wiki/vertex1');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');
        $crawler = $client->request('GET', $url);
        $this->assertPageTitleContains('vertex1');
        $pk = $client->getRequest()->get('pk');

        return $pk;
    }

    /** @depends testTopBarMenuLink */
    public function testNavigationPrevious(string $pk)
    {
        $client = static::createClient();
        $client->request('GET', '/vertex/previous/' . $pk);
        $this->assertPageTitleContains('vertex2');
    }

    /** @depends testTopBarMenuLink */
    public function testNavigationNext(string $pk)
    {
        $client = static::createClient();
        $client->request('GET', '/vertex/next/' . $pk);
        $this->assertPageTitleContains('vertex0');
    }

    public function testRename()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/wiki/vertex0');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-rename"]/parent::a')->attr('href');
        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('form_rename')->form();
        $form['form[title]'] = 'vertex3';
        $crawler = $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertPageTitleContains('vertex3');
    }

    public function testDelete()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/filter');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-trash-empty"]/parent::a')->attr('href');

        $crawler = $client->request('GET', $url);
        $form = $crawler->selectButton('form_delete')->form();
        $crawler = $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
    }

}
