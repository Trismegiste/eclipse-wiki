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

    public function testClean()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, iterator_to_array($this->repository->search()));
    }

    public function testCreateToken()
    {
        $npc = new Transhuman('tmp', new Background('back'), new Faction('fact'));
        $this->repository->save($npc);

        $crawler = $this->client->request('GET', '/npc/token/' . $npc->getPk());
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

    /** @depends testCreateToken */
    public function testUnique(string $pk)
    {
        ob_start();
        $this->client->request('GET', '/profile/unique/' . $pk);
        ob_end_clean();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('image/png', $this->client->getResponse()->headers->get('content-type'));
    }

    /** @depends testCreateToken */
    public function testPushUnique(string $pk)
    {
        $this->client->request('POST', '/profile/unique/' . $pk);
        $this->assertResponseIsSuccessful();
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('success', $result->level);
    }

    /** @depends testCreateToken */
    public function testTemplateChoices(string $pk)
    {
        $npc = $this->repository->load($pk);
        $npc->surnameLang = 'japanese';
        $this->repository->save($npc);
        $this->client->request('GET', '/profile/template/' . $pk);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[data-avatar]');

        return $pk;
    }

    /** @depends testTemplateChoices */
    public function testPushTemplate(string $pk)
    {
        $crawler = $this->client->request('GET', '/profile/template/' . $pk);
        $this->assertSelectorExists('#profile_on_the_fly_generate');
        $form = $crawler->selectButton('profile_on_the_fly_generate')->form();

        $form ['profile_on_the_fly[name]'] = 'Yolo';

        $filename = 'tmp.png';
        $image = $this->createTestChart(256);
        imagepng($image, $filename);

        $form['profile_on_the_fly[avatar]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        unlink($filename);

        $this->assertResponseIsSuccessful();
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('success', $result->level);
    }

}
