<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VoronoiCrudTest extends WebTestCase
{

    protected Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    protected \App\Repository\VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(\App\Repository\VertexRepository::class);
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
    }

    /** @depends testCreate */
    public function testShow(string $pk)
    {
        $this->client->request('GET', "/vertex/show/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('article img');

        return $pk;
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
    }

}
