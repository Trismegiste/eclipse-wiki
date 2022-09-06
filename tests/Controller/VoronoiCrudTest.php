<?php

/*
 * eclipse-wiki
 */

use App\Entity\Place;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        $crawler = $this->client->request('GET', '/voronoi/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('map_config_create')->form();
        $form->setValues(['map_config' => [
                'title' => 'Test',
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

        $form = $crawler->selectButton('map_config_create')->form();
        $form->setValues(['map_config' => [
                'side' => 20,
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
    public function testShow(string $pk)
    {
        $this->client->request('GET', "/vertex/show/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('article img');

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
        $this->client->request('GET', "/voronoi/texture/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /** @depends testShow */
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
        $this->client->request('GET', "/voronoi/running/$pk");
        $this->assertResponseIsSuccessful();

        return $pk;
    }

    /** @depends testGenerateSvg */
    public function testAttachPlace(string $pk)
    {
        $crawler = $this->client->request('GET', "/voronoi/attachplace/$pk");
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('generate_map_for_place_attach')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $target = $this->client->getResponse()->headers->get('location');
        $this->assertStringStartsWith('/vertex/rename/', $target);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $pk = $this->client->getRequest()->attributes->get('pk');

        return $pk;
    }

    /** @depends testAttachPlace */
    public function testNewPlace(string $pk)
    {
        $place = $this->repository->load($pk);
        $this->assertInstanceOf(Place::class, $place);
        $this->assertNotNull($place->battleMap);

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

    /** @depends testNewPlace */
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
        $this->client->request('GET', "/place/battlemap/$pk");
        $this->assertResponseIsSuccessful();
    }

}
