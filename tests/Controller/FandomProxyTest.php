<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FandomProxyTest extends WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSearch()
    {
        $this->client->request('GET', '/fandom/search');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#fandom_search_search');
        $crawler = $this->client->submitForm('fandom_search_search', ['fandom_search' => ['query' => 'mars', 'namespace' => 'page']], 'GET');
        $this->assertResponseIsSuccessful();
        $result = $crawler->filter('main .result a');
        $this->assertGreaterThanOrEqual(1, count($result)); // at least there is one

        return $result->first()->link();
    }

    /** @depends testSearch */
    public function testShow(Symfony\Component\DomCrawler\Link $link): int
    {
        $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('main', 'Mars');

        return $this->client->getRequest()->attributes->get('id');
    }

    /** @depends testShow */
    public function testPdf(int $id)
    {
        $this->client->request('GET', "/fandom/pdf/$id");
        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('content-type'));
    }

    /** @depends testShow */
    public function testPushPdf(int $id)
    {
        $this->client->request('GET', "/fandom/push/$id");
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertStringContainsString('Aide Fandom envoy', $this->client->getResponse()->getContent());
    }

}
