<?php

/*
 * Eclipse Wiki
 */

use App\Repository\VertexRepository;
use App\Service\Storage;
use App\Tests\Controller\PictureFixture;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameSessionTest extends WebTestCase
{

    use PictureFixture;

    protected KernelBrowser $client;
    protected VertexRepository $repository;
    protected Storage $storage;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
        $this->storage = static::getContainer()->get(Storage::class);
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, iterator_to_array($this->repository->search()));
    }

    public function testPictureResponse()
    {
        $filename = join_paths($this->storage->getRootDir(), 'history.png');
        $image = $this->createTestChart(1024);
        imagepng($image, $filename);

        $this->client->request('GET', '/picture/get/history.png');
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('image/png', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testPicturePush()
    {
        $this->client->request('POST', '/picture/push/history.png');
        $this->assertResponseIsSuccessful();
        $ret = json_decode($this->client->getResponse()->getContent());
        $this->assertStringContainsString('history.png', $ret->message);
        $this->assertStringContainsString('sent', $ret->message);
    }

    public function testHistoryBroadcastExport()
    {
        $crawler = $this->client->request('GET', '/session/broadcast-export');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('script[type="application/json"]', 'history.png');
        $this->assertSelectorExists('#gallery_selection_export');
    }

    public function testBroadcastedPicture()
    {
        $this->client->request('GET', '/session/broadcasted-picture/history.png.jpg');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);
        $this->assertEquals('image/jpeg', $this->client->getResponse()->headers->get('Content-Type'));
    }

}
