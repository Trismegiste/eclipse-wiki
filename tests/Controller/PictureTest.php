<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Entity\Vertex;
use App\Repository\VertexRepository;
use App\Service\Storage;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function join_paths;

class PictureTest extends WebTestCase
{

    use PictureFixture;

    protected KernelBrowser $client;
    protected Storage $storage;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->storage = static::getContainer()->get(Storage::class);
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, iterator_to_array($this->repository->search()));
    }

    public function testPictureResponse()
    {
        $filename = join_paths($this->storage->getRootDir(), 'yolo.png');
        $image = $this->createTestChart(1024);
        imagepng($image, $filename);

        $this->client->request('GET', '/picture/get/yolo.png');
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('image/png', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testSearch()
    {
        $this->client->request('GET', '/picture/search?q=yoLo');
        $this->assertResponseIsSuccessful();
        $listing = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(1, $listing);
        $this->assertEquals('yolo.png', $listing[0]);
    }

    public function testPicturePush()
    {
        $this->client->request('POST', '/picture/push/yolo.png');
        $this->assertResponseIsSuccessful();
        $ret = json_decode($this->client->getResponse()->getContent());
        $this->assertStringContainsString('yolo.png', $ret->message);
        $this->assertStringContainsString('sent', $ret->message);
    }

    public function getVertices(): array
    {
        return [
            [$this->createRandomTimeline()],
            [$this->createRandomScene()],
            [$this->createRandomPlace()],
            [$this->createRandomHandout()],
            [$this->createRandomLoveletter()],
            [$this->createRandomTranshuman()]
        ];
    }

    /** @dataProvider getVertices */
    public function testUpload(Vertex $target)
    {
        $target->setContent('information');
        $this->repository->save($target);

        $crawler = $this->client->request('GET', '/picture/upload');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('append_picture_upload_upload')->form();
        $form->setValues(['append_picture_upload' => [
                'filename' => 'uploaded',
                'append_vertex' => $target->getPk()
        ]]);
        try {
            $this->storage->delete('uploaded.jpg');
        } catch (Exception $e) {
            // silent bug
        }

        $filename = 'tmp.png';
        $image = $this->createTestChart(2000); // to force resizing
        imagepng($image, $filename);

        $form['append_picture_upload[picture]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        unlink($filename);

        $updated = $this->repository->load($target->getPk());
        $this->assertStringContainsString('information', $updated->getContent());
        $this->assertStringContainsString('[[file:uploaded.jpg]]', $updated->getContent());
    }

    public function testPictogram()
    {
        $this->client->request('GET', '/picto/get?title=processor');
        $this->assertStringStartsWith('<?xml', $this->client->getResponse()->getContent());
    }

    public function testDynamicVertexList()
    {
        $this->client->request('GET', '/picture/vertex/search?q=tak');
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(1, $result);
        $this->assertStringStartsWith('Takeshi', $result[0]->title);
    }

    public function testBrokenPicture()
    {
        $this->client->request('GET', '/picture/broken');
        $this->assertResponseIsSuccessful();
    }

    public function testMissingPictureUpload()
    {
        $crawler = $this->client->request('GET', '/picture/missing/The Shrike' . rand() . '.jpg/upload');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#missing_picture_upload_upload');
        $form = $crawler->selectButton('missing_picture_upload_upload')->form();

        $filename = sys_get_temp_dir() . '/tmp.png';
        $image = $this->createTestChart(500);
        imagepng($image, $filename);

        $form['missing_picture_upload[picture]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

}
