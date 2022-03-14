<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

class PictureTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{

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

    public function testCreateProfile()
    {
        $npc = new \App\Entity\Transhuman('tmp', new \App\Entity\Background('back'), new \App\Entity\Faction('fact'));
        self::getContainer()->get(\App\Repository\VertexRepository::class)->save($npc);
        $crawler = $this->client->request('GET', '/profile/create/' . $npc->getPk());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('profile_pic_generate')->form();

        $filename = 'tmp.png';
        $image = $this->createTestChart(256);
        imagepng($image, $filename);

        $form['profile_pic[avatar]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        unlink($filename);
    }

    protected function createTestChart(int $side)
    {
        $target = imagecreatetruecolor($side, $side);
        $bg = imagecolorallocate($target, 0xff, 0xff, 0xff);
        imagefill($target, 0, 0, $bg);

        $fg = imagecolorallocate($target, 0xff, 0, 0);
        imagefilledellipse($target, $side / 2, $side / 2, $side / 2, $side / 2, $fg);

        return $target;
    }

    public function testPicturePush()
    {
        $this->client->request('POST', '/picture/push/yolo.png');
        $this->assertResponseIsSuccessful();
        $ret = json_decode($this->client->getResponse()->getContent());
        $this->assertStringContainsString('yolo.png', $ret->message);
        $this->assertStringContainsString('sent', $ret->message);
    }

}
