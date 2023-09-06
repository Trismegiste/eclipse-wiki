<?php

/*
 * eclipse-wiki
 */

use App\Entity\MapConfig;
use App\Entity\Place;
use App\Repository\VertexRepository;
use MongoDB\BSON\ObjectId;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FirstPersonTest extends WebTestCase
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
        $place = new Place('3dmap');
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
    public function testJsonBattlemap(string $pk)
    {
        $this->client->request('GET', "/fps/scene/$pk.battlemap");
        $this->assertResponseIsSuccessful();
    }

    /** @depends testCreate */
    public function testEdit(string $pk)
    {
        $this->client->request('GET', "/fps/edit/$pk");
        $this->assertSelectorNotExists("a[href='/fps/delete/$pk']", 'The button should not be here since there is no json battlemap document yet');
        $this->assertResponseIsSuccessful();
    }

    public function testPlayerView()
    {
        $this->client->request('GET', "/player/fps");
        $this->assertResponseIsSuccessful();
    }

    /** @depends testCreate */
    public function testWrite(string $pk)
    {
        $store = static::getContainer()->get(App\Service\Storage::class);
        $doc = join_paths($store->getRootDir(), "map3d-$pk.json");
        @unlink($doc);

        $crawler = $this->client->request('GET', "/fps/edit/$pk");
        $this->assertSelectorExists('#battlemap3d_write_write');
        $form = $crawler->selectButton('battlemap3d_write_write')->form();
        $this->client->submit($form, [
            'battlemap3d_write' => [
                'battlemap3d' => '{"theme":"habitat", "side":10, "npcToken":[], "grid":[]}'
            ]
        ]);
        $this->assertResponseIsSuccessful();

        $place = $this->repository->findByPk($pk);
        $this->assertNotEmpty($place->battlemap3d);

        // storage
        $this->assertFileExists($doc);

        return $pk;
    }

    /** @depends testCreate */
    public function testBroadcast(string $pk)
    {
        $crawler = $this->client->request('GET', "/fps/edit/$pk");
        $this->assertSelectorExists('#cubemap_broadcast_send');
        $form = $crawler->selectButton('cubemap_broadcast_send')->form();

        $filename = join_paths(sys_get_temp_dir(), 'tmp.png');
        $image = imagecreatetruecolor(50, 50);
        imagepng($image, $filename);

        for ($k = 0; $k < 6; $k++) {
            $form["cubemap_broadcast[picture][$k]"]->upload($filename);
        }
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('success', $response->level);
    }

    /** @depends testWrite */
    public function testBattlemapThumbnail(string $pk)
    {
        $this->client->request('GET', "/battlemap/thumbnail/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertEquals('image/jpeg', $this->client->getResponse()->headers->get('Content-Type'));

        return $pk;
    }

    /** @depends testBattlemapThumbnail */
    public function testResetMap(string $pk)
    {
        $this->client->request('GET', "/fps/edit/$pk");
        $this->assertSelectorExists("a[href='/fps/delete/$pk']", 'The button should be here since the json battlemap document has been written');
        $crawler = $this->client->request('GET', "/fps/delete/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form button#form_delete');
        $form = $crawler->selectButton('form_delete')->form();
        $this->client->submit($form);

        $place = $this->repository->findByPk($pk);
        $this->assertEmpty($place->battlemap3d);
    }

}
