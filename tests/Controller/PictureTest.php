<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function join_paths;

class PictureTest extends WebTestCase
{

    use PictureFixture;

    protected KernelBrowser $client;
    protected Storage $storage;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->storage = static::getContainer()->get(Storage::class);
    }

    public function testPictureResponse()
    {
        $filename = join_paths(static::getContainer()->get(Storage::class)->getRootDir(), 'yolo.png');
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
        $this->assertStringContainsString('complete', $ret->message);
    }

    public function testUpload()
    {
        $crawler = $this->client->request('GET', '/picture/upload');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('picture_upload_upload')->form();
        $form->setValues(['picture_upload' => [
                'filename' => 'uploaded'
        ]]);
        try {
            $this->storage->delete('uploaded.jpg');
        } catch (\Exception $e) {
	    // silent bug
        }

        $filename = 'tmp.png';
        $image = $this->createTestChart(2000); // to force resizing
        imagepng($image, $filename);

        $form['picture_upload[picture]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        unlink($filename);
    }

    public function testPictogram()
    {
        $this->client->request('GET', '/picto/get?title=processor');
        $this->assertStringStartsWith('<g', $this->client->getResponse()->getContent());
    }

    public function testToken()
    {
        $npc = new \App\Entity\Transhuman('battle', new \App\Entity\Background('bg'), new \App\Entity\Faction('fac'));
        $repo = static::getContainer()->get(\App\Repository\VertexRepository::class);
        $npc->tokenPic = 'yolo.png'; // created above
        $repo->save($npc);
        ob_start();
        $this->client->request('GET', '/token/get?title=battle');
        ob_end_clean();
        $this->assertEquals('image/svg+xml', $this->client->getResponse()->headers->get('content-type'));
    }

}
