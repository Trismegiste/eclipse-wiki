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
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testCreateProfile()
    {
        $npc = new Transhuman('tmp', new Background('back'), new Faction('fact'));
        $this->repository->save($npc);
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

        return (string) $npc->getPk();
    }

    /** @depends testCreateProfile */
    public function testShowProfileOnTheFly(string $pk)
    {
        $npc = $this->repository->load($pk);
        $npc->surnameLang = 'japanese';
        $this->repository->save($npc);
        $crawler = $this->client->request('GET', '/profile/onthefly/' . $pk);
        $this->assertResponseIsSuccessful();

        return $pk;
    }

    /** @depends testShowProfileOnTheFly */
    public function testPushProfileOnTheFly(string $pk)
    {
        $crawler = $this->client->request('GET', '/profile/onthefly/' . $pk);
        $this->assertSelectorExists('#profile_on_the_fly_generate');
        $this->client->submitForm('profile_on_the_fly_generate', [
                'profile_on_the_fly' => [
                'svg' => '<svg width="256" height="256" viewBox="0 0 256 256"/>',
                'name' => 'Yolo',
                'template' => $pk
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('success', $result->level);
    }

}
