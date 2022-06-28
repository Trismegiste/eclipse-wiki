<?php

/*
 * eclipse-wiki
 */

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfilePictureTest extends WebTestCase
{

    use \App\Tests\Controller\PictureFixture;

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreateProfile()
    {
        $npc = new Transhuman('tmp', new Background('back'), new Faction('fact'));
        self::getContainer()->get(VertexRepository::class)->save($npc);
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

}
