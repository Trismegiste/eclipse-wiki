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

    public function testShowNotFound()
    {
        $crawler = $this->client->request('GET', '/picture/popup/notfound.jpg');
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('/img/mire.svg', $crawler->filter('img')->first()->attr('src'));
    }

    public function testShow()
    {
        $filename = \join_paths(static::getContainer()->get(\App\Service\Storage::class)->getRootDir(), 'yolo.png');
        $image = imagecreatetruecolor(200, 200);
        imagepng($image, $filename);

        $this->client->request('GET', '/picture/get/yolo.png');
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('image/png', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testSearch()
    {
        $this->client->request('GET', '/picture/search?q=yolo');
        $this->assertResponseIsSuccessful();
        $listing = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(1, $listing);
        $this->assertEquals('yolo.png', $listing[0]);
    }

    public function testSendBluetooth()
    {
        $this->client->request('GET', '/picture/send/notfound.jpg');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateProfile()
    {
        $npc = new \App\Entity\Transhuman('tmp', new \App\Entity\Background('back'), new \App\Entity\Faction('fact'));
        self::getContainer()->get(\App\Repository\VertexRepository::class)->save($npc);
        $crawler = $this->client->request('GET', '/profile/create/' . $npc->getPk());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('profile_pic_generate')->form();

        $filename = 'tmp.png';
        $image = imagecreatetruecolor(200, 200);
        imagepng($image, $filename);

        $form['profile_pic[avatar]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        unlink($filename);
    }

}
