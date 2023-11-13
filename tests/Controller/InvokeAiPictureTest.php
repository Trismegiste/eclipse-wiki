<?php

use App\Entity\Place;
use App\Repository\VertexRepository;
use App\Service\StableDiffusion\LocalRepository;
use App\Tests\Service\StableDiffusion\PngReaderTest;
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
    {     // inbsert
        $storage = self::getContainer()->get(LocalRepository::class);
        $folder = __DIR__ . '/../fixtures';
        $src = join_paths($folder, PngReaderTest::fixture);
        $dst = join_paths($storage->getRootDir(), PngReaderTest::fixture);
        copy($src, $dst);

        $crawler = $this->client->request('GET', '/invokeai/search');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#form_search');
        $form = $crawler->selectButton('form_search')->form();
        $form->setValues(['form' => ['query' => 'strawberry']]);
        $this->client->submit($form);
        $this->assertSelectorCount(1, '.search .thumbnail img');
        @unlink($dst);
    }

    public function testAppendPictureToPlace()
    {
        $place = new Place('noimage' . rand());
        $this->repository->save($place);
        $crawler = $this->client->request('GET', '/invokeai/vertex/' . $place->getPk() . '/search');
        $this->assertResponseIsSuccessful();
    }

}
