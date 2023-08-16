<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\Strangelove\MongoDb\Repository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Repository\VertexRepository;

class InvokeAiPictureTest extends WebTestCase
{

    protected KernelBrowser $client;
    protected Repository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testSearch()
    {
        $this->client->request('GET', '/invokeai/search');
        $this->assertResponseIsSuccessful();
    }

    public function appendPictureToPlace()
    {
        $place = new Place('noimage');
        $this->repository->save($place);
        $crawler = $this->client->request('GET', '/invokeai/vertex/' . $place->getPk() . '/append');
        $this->assertResponseIsSuccessful();
    }

}
