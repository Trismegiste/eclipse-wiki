<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoveletterCrudTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/loveletter/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('loveletter_create')->form();
        $form->setValues(['loveletter' => [
                'title' => 'Love letter',
                'content' => 'Some link to [[Luke]] and [[file:luke.jpg]]',
                "player" => "ABCD",
                "drama" => "789",
                "roll1" => ["trait" => "Agilité", "difficulty" => -1],
                "roll2" => ["trait" => "Agilité", "difficulty" => -1],
                "roll3" => ["trait" => "Agilité", "difficulty" => -1],
                "resolution" => [
                    "choice1",
                    "choice2",
                    "choice3",
                    "choice4"
                ]
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testCreateWithTitle()
    {
        $crawler = $this->client->request('GET', '/loveletter/create?title=alderaan');
        $form = $crawler->selectButton('loveletter_create')->form();
        $this->assertEquals('Alderaan', $form['loveletter']['title']->getValue());
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
        $this->assertPageTitleContains('Love letter');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertPageTitleContains('Love letter');
        $this->assertCount(1, $crawler->selectButton('loveletter_create'));
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-file-pdf"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testEdit */
    public function testPdf(string $pdf)
    {
        $this->client->request('GET', $pdf);
        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-type'));
    }

    /** @depends testShow */
    public function testSelect(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-select-list"]/parent::a')->attr('href');

        $crawler = $this->client->request('GET', $url);
        $this->assertPageTitleContains('Love letter');
        $button = $crawler->selectButton('loveletter_pc_choice_select');
        $this->assertCount(1, $button);

        $form = $button->form();
        $form->setValues(['loveletter_pc_choice' => [
                'pc_choice' => [
                    0, 1, 2
                ]
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

}
