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
        $this->client->request('GET', "/fps/babylon/$pk.battlemap");
        $this->assertResponseIsSuccessful();
    }

    /** @depends testCreate */
    public function testExplore(string $pk)
    {
        $this->client->request('GET', "/fps/explore/$pk");
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

        $crawler = $this->client->request('GET', "/fps/explore/$pk");
        $this->assertSelectorExists('#battlemap3d_write_write');
        $form = $crawler->selectButton('battlemap3d_write_write')->form();
        $this->client->submit($form, [
            'battlemap3d_write' => [
                'battlemap3d' => '{"a":1}'
            ]
        ]);
        $this->assertResponseIsSuccessful();

        $place = $this->repository->findByPk($pk);
        $this->assertNotEmpty($place->battlemap3d);

        // storage
        $this->assertFileExists($doc);
    }

    /** @depends testCreate */
    public function testBroadcast(string $pk)
    {
        $crawler = $this->client->request('GET', "/fps/explore/$pk");
        $this->assertSelectorExists('#cubemap_broadcast_send');
        $form = $crawler->selectButton('cubemap_broadcast_send')->form();

        $filename = join_paths(sys_get_temp_dir(), 'tmp.png');
        $image = imagecreatetruecolor(50, 50);
        imagepng($image, $filename);

        for ($k = 0; $k < 6; $k++) {
            $form["cubemap_broadcast[picture][$k]"]->upload($filename);
        }
        $this->client->submit($form);
    }

}
