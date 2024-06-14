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
        $factory = static::getContainer()->get(\App\Repository\CharacterFactory::class);
        /** @var \App\Entity\Transhuman $npc */
        $npc = $factory->create('tmp', new Background('back'), new Faction('fact'));
        $npc->setMorph(new App\Entity\Morph('morph'));
        $this->repository->save($npc);

        $crawler = $this->client->request('GET', '/npc/token/' . $npc->getPk());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('profile_pic_generate')->form();

        $filename = 'tmp.png';
        $image = $this->createTestChart(256);
        imagepng($image, $filename);

        $form['profile_pic[avatar]']->upload($filename);
        $this->client->submit($form);
        $this->assertResponseRedirects('/vertex/show/' . $npc->getPk());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        unlink($filename);

        return (string) $npc->getPk();
    }

    /** @depends testCreateToken */
    public function testUnique(string $pk)
    {
        $this->client->request('GET', '/profile/unique/' . $pk);
        $this->assertResponseIsSuccessful();

        $resp = $this->client->getResponse();
        $this->assertEquals('image/png', $resp->headers->get('content-type'));
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
        $this->assertSelectorExists('canvas');

        return $pk;
    }

    /** @depends testTemplateChoices */
    public function testPushTemplate(string $pk)
    {
        $crawler = $this->client->request('GET', '/profile/template/' . $pk);
        $this->assertSelectorExists('#profile_on_the_fly_push_profile');
        $form = $crawler->selectButton('profile_on_the_fly_push_profile')->form();

        $form ['profile_on_the_fly[title]'] = 'Yolo';

        $filename = 'tmp.png';
        $image = $this->createTestChart(256);
        imagepng($image, $filename);

        $form['profile_on_the_fly[avatar]']->upload($filename);
        $this->client->submit($form);
        unlink($filename);

        $this->assertResponseRedirects('/profile/template/' . $pk);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    /** @depends testTemplateChoices */
    public function testInstantiateTemplate(string $pk)
    {
        $crawler = $this->client->request('GET', '/profile/template/' . $pk);
        $this->assertSelectorExists('#profile_on_the_fly_instantiate_npc');
        $form = $crawler->selectButton('profile_on_the_fly_instantiate_npc')->form();

        $form ['profile_on_the_fly[title]'] = 'New Extra';

        $filename = 'tmp.png';
        $image = $this->createTestChart(256);
        imagepng($image, $filename);

        $form['profile_on_the_fly[avatar]']->upload($filename);
        $this->client->submit($form);
        unlink($filename);

        $this->assertResponseRedirects();
        $this->assertStringStartsWith('/vertex/show', $this->client->getResponse()->headers->get('location'));
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

}
