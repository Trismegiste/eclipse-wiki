<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HandoutCrudTest extends WebTestCase
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
        $crawler = $this->client->request('GET', '/handout/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('handout_create')->form();
        $form->setValues(['handout' => [
                'title' => 'Handout1',
                'content' => 'Info for PC',
                "target" => "ABCD",
                "gm_info" => "Info for GM",
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testCreateWithTitle()
    {
        $crawler = $this->client->request('GET', '/handout/create?title=help');
        $form = $crawler->selectButton('handout_create')->form();
        $this->assertEquals('Help', $form['handout']['title']->getValue());
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
        $this->assertPageTitleContains('Handout1');
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-edit"]/parent::a')->attr('href');

        return $url;
    }

    /** @depends testShow */
    public function testEdit(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $this->assertPageTitleContains('Handout1');
        $this->assertCount(1, $crawler->selectButton('handout_create'));
    }

    /** @depends testShow */
    public function testQrCode(string $edit)
    {
        $crawler = $this->client->request('GET', $edit);
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-qrcode"]/parent::a')->attr('href');

        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('QRious', $this->client->getResponse()->getContent());
    }

}
