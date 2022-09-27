<?php

/*
 * eclipse-wiki
 */

use App\Entity\Place;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Morph;

class VoronoiCrudTest extends WebTestCase
{

    protected KernelBrowser $client;
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

    public function testCreate()
    {
        $place = new Place('Empty');
        $this->repository->save($place);

        $crawler = $this->client->request('GET', '/voronoi/edit/' . $place->getPk());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('form_generate')->form();
        $form->setValues(['form[voronoiParam]' => [
                'side' => 20,
                'seed' => 666,
                'avgTilePerRoom' => 12,
                'horizontalLines' => 0,
                'verticalLines' => 0
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $pk = $this->client->getRequest()->attributes->get('pk');

        return $pk;
    }

    /** @depends testCreate */
    public function testEdit(string $pk)
    {
        $crawler = $this->client->request('GET', "/voronoi/edit/$pk");
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('form_generate')->form();
        $form->setValues(['form[voronoiParam]' => [
                'side' => 25,
                'seed' => 666,
                'avgTilePerRoom' => 12,
                'horizontalLines' => 0,
                'verticalLines' => 0,
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();

        return $pk;
    }

    /** @depends testCreate */
    public function testStorage(string $pk)
    {
        $crawler = $this->client->request('GET', "/voronoi/storage/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('article img');
        $form = $crawler->selectButton('generate_map_for_place_store_battlemap')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();

        return $pk;
    }

    /** @depends testEdit */
    public function testStatistics(string $pk)
    {
        $this->client->request('GET', "/voronoi/statistics/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /** @depends testEdit */
    public function testTextures(string $pk)
    {
        $crawler = $this->client->request('GET', "/voronoi/texture/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('form_texture')->form();
        $form->setValues(['form[voronoiParam][tileWeight]' => [
                'cluster-sleep' => 5
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();

        return $pk;
    }

    /** @depends testEdit */
    public function testGenerateSvg(string $pk)
    {
        ob_start();
        $this->client->request('GET', "/voronoi/generate/$pk");
        ob_end_clean();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('image/svg+xml', $this->client->getResponse()->headers->get('content-type'));

        return $pk;
    }

    /** @depends testGenerateSvg */
    public function testRunningOnTheFly(string $pk)
    {
        $this->client->request('GET', "/voronoi/runmap/$pk");
        $this->assertResponseIsSuccessful();

        return $pk;
    }

    /** @depends testRunningOnTheFly */
    public function testPushPlayerView(string $pk)
    {
        ob_start();
        $this->client->request('GET', "/voronoi/generate/$pk");
        $map = ob_get_clean();
        $target = 'tmp.svg';
        file_put_contents($target, $map);

        $this->client->request('POST', "/voronoi/broadcast", [], ['svg' => new UploadedFile($target, $target)]);
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse()->getContent();
        $this->assertJson($response);
        $response = json_decode($response);
        $this->assertEquals('success', $response->level);
        $this->assertStringContainsString('Broadcast', $response->message);
    }

    /** @depends testStorage */
    public function testBattlemapThumbnail(string $pk)
    {
        $this->client->request('GET', "/battlemap/thumbnail/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertEquals('image/jpeg', $this->client->getResponse()->headers->get('Content-Type'));

        return $pk;
    }

    /** @depends testBattlemapThumbnail */
    public function testRunningBattlemap(string $pk)
    {
        $this->client->request('GET', "/place/runmap/$pk");
        $this->assertResponseIsSuccessful();
    }

    /** @depends testTextures */
    public function testPopulate(string $pk)
    {
        // fixtures
        $fac = static::getContainer()->get(\App\Repository\CharacterFactory::class);
        $npc = $fac->create('Wizard', new Background('back'), new Faction('fact'));
        $npc->setMorph(new Morph('morph'));
        $npc->tokenPic = 'Wizard-token.png';
        $npc->surnameLang = 'english';
        $this->repository->save($npc);

        $crawler = $this->client->request('GET', "/voronoi/populate/$pk");
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('form_populate')->form();
        $form->setValues(['form[voronoiParam][tilePopulation]' => [
                'cluster-sleep' => [
                    'npc' => 'Wizard',
                    'tilePerNpc' => 6
                ]
        ]]);
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();

        return $pk;
    }

    /** @depends testPopulate */
    public function testGenerateSvgWithNpc(string $pk)
    {
        ob_start();
        $this->client->request('GET', "/voronoi/generate/$pk");
        ob_end_clean();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('image/svg+xml', $this->client->getResponse()->headers->get('content-type'));

        return $pk;
    }

    /** @depends testGenerateSvgWithNpc */
    public function testStatsWithNpc(string $pk)
    {
        $this->client->request('GET', "/voronoi/statistics/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'Wizard');
        $this->assertSelectorTextContains('a[href^="/place/npc/Wizard"]', 'Wizard');
    }

    /** @depends testStatsWithNpc */
    public function testShowNpc()
    {
        $this->client->request('GET', "/place/npc/Wizard");
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
	$this->assertEquals('app_profilepicture_generateonthefly', $this->client->getRequest()->get('_route'));
    }

}
