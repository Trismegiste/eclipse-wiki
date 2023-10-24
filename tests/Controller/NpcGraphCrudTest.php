<?php

/*
 * eclipse-wiki
 */

use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

class NpcGraphCrudTest extends WebTestCase
{

    protected KernelBrowser $client;
    protected Repository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testEdit()
    {
        $crawler = $this->client->request('GET', '/npc/graph/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#full_tree_save');
    }

    public function testRun()
    {
        $crawler = $this->client->request('GET', '/npc/graph/run');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#selector_generate');
    }

}
