<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Repository\VertexRepository;

class OllamaTest extends WebTestCase
{
    use \App\Tests\Controller\PictureFixture;

    protected KernelBrowser $client;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testNpcBackground()
    {
        $npc = $this->createRandomTranshuman();
        $this->repository->save($npc);
        $pk = $npc->getPk();
        $this->client->request('GET', "/ollama/npc/$pk/background");
        $this->assertResponseIsSuccessful();
    }

}
