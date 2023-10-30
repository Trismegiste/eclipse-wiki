<?php

use App\Entity\Place;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

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

    public function testAppendPictureToPlace()
    {
        $place = new Place('noimage' . rand());
        $this->repository->save($place);
        $crawler = $this->client->request('GET', '/invokeai/vertex/' . $place->getPk() . '/search');
        $this->assertResponseIsSuccessful();
    }

}
