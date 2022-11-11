<?php

/*
 * eclipse-wiki
 */

use Symfony\Component\DomCrawler\Crawler;

class RemotePictureTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSearch()
    {
        $this->client->request('GET', '/remote/search');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#form_search');
        $crawler = $this->client->submitForm('form_search', ['form[query]' => 'mars'], 'GET');
        $this->assertResponseIsSuccessful();
        $result = $crawler->filter('.pushable a');
        $this->assertGreaterThanOrEqual(1, count($result)); // at least there is one

        return $result->first();
    }

    /** @depends testSearch */
    public function testGetRemoteImg(Crawler $node)
    {
        $img = $node->filter('img')->first()->attr('src');
        $this->client->request('GET', $img);
        $this->assertResponseIsSuccessful();
        $this->assertStringStartsWith('image/', $this->client->getResponse()->headers->get('content-type'));
    }

    /** @depends testSearch */
    public function testPushRemoteImg(Crawler $node)
    {
        $pushing = $node->attr('href');
        $this->client->request('POST', $pushing);
        $this->assertResponseIsSuccessful();
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('success', $result->level);
    }

}
