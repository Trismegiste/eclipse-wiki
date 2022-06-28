<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

class PictureTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

    use PictureFixture;

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testPictureResponse()
    {
        $filename = \join_paths(static::getContainer()->get(\App\Service\Storage::class)->getRootDir(), 'yolo.png');
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

}
