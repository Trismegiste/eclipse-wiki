<?php

/*
 * eclipse-wiki
 */

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
    }

}
