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
        $this->client->request('GET', "/ollama/vertex/$pk/generate/npc-bg");
        $this->assertResponseIsSuccessful();
    }

    public function testBar()
    {
        $v = $this->createRandomPlace();
        $this->repository->save($v);
        $pk = $v->getPk();
        $this->client->request('GET', "/ollama/vertex/$pk/generate/bar");
        $this->assertResponseIsSuccessful();
    }

    public function getListingKey()
    {
        return [
            ['npc-name'],
            ['thing-name'],
        ];
    }

    /** @dataProvider getListingKey */
    public function testListingGeneration(string $key)
    {
        $this->client->request('GET', "/ollama/creation/listing/$key");
        $this->assertResponseIsSuccessful();
    }

}
