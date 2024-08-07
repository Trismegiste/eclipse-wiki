<?php

/*
 * eclipse-wiki
 */

use App\Tests\Service\Pdf\PdfAssert;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Link;

class FandomProxyTest extends WebTestCase
{

    use PdfAssert;

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
    public function testShow(Link $link): int
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
        $this->assertResponsePdf($this->client->getResponse());
    }

    /** @depends testShow */
    public function testPushPdf(int $id)
    {
        $this->client->request('GET', "/fandom/push/$id");
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertStringContainsString('Aide Fandom envoy', $this->client->getResponse()->getContent());
    }

    public function testAutocomplete()
    {
        $this->client->request('GET', "/fandom/autocomplete?q=mars");
        $this->assertResponseIsSuccessful();
        $found = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(5, $found);
    }

    public function testAutocompleteCutoff()
    {
        $this->client->request('GET', "/fandom/autocomplete?q=ma");
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, json_decode($this->client->getResponse()->getContent()));
    }

}
