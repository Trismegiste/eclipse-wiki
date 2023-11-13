<?php

use App\Entity\Place;
use App\Repository\VertexRepository;
use App\Service\StableDiffusion\LocalRepository;
use App\Tests\Service\StableDiffusion\PngReaderTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

class InvokeAiPictureTest extends WebTestCase
{

    protected KernelBrowser $client;
    protected Repository $repository;
    protected LocalRepository $storage;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
        $this->storage = static::getContainer()->get(LocalRepository::class);
    }

    public function testSearch()
    {     // inbsert
        $folder = __DIR__ . '/../fixtures';
        $src = join_paths($folder, PngReaderTest::fixture);
        $dst = join_paths($this->storage->getRootDir(), PngReaderTest::fixture);
        copy($src, $dst);

        $crawler = $this->client->request('GET', '/invokeai/search');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#form_search');
        $form = $crawler->selectButton('form_search')->form();
        $form->setValues(['form' => ['query' => 'strawberry']]);
        $crawler = $this->client->submit($form);
        $result = $crawler->filter('.search .thumbnail img');
        $this->assertCount(1, $result);
        $thumb = $result->attr('src');
        $link = $result->ancestors()->first();
        $this->assertEquals('strawberry', $link->attr('title'));
        $this->assertStringStartsWith('file://', $link->attr('href'));
    }

    /** @depends testSearch */
    public function testAjaxSearch()
    {
        $this->client->request('GET', '/invokeai/ajax/search?q=strawberry');
        $result = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(1, $result->local);

        return $result->local[0]->thumb;
    }

    /** @depends testAjaxSearch */
    public function testGetLocalPicture(string $thumbUri)
    {
        $this->client->request('GET', $thumbUri);
        $this->assertResponseIsSuccessful();
        $this->assertEquals('image/png', $this->client->getResponse()->headers->get('Content-Type'));
        ob_start();
        $this->client->getResponse()->sendContent();
        $content = ob_get_clean();
        $image = imagecreatefromstring($content);
        $this->assertEquals(128, imagesx($image));

        $name = $this->client->getRequest()->get('pic');
        $this->assertEquals(PngReaderTest::fixture, $name . '.png');
    }

    /** @depends testGetLocalPicture */
    public function testSearchPictureForPlace()
    {
        $place = new Place('noimage' . rand());
        $this->repository->save($place);
        $crawler = $this->client->request('GET', '/invokeai/vertex/' . $place->getPk() . '/search');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#form_search');
        $form = $crawler->selectButton('form_search')->form();
        $form->setValues(['form' => ['query' => 'strawberry']]);
        $crawler = $this->client->submit($form);

        $result = $crawler->filter('.search .thumbnail img');
        $this->assertCount(1, $result);

        return $result->ancestors()->first()->link();
    }

    /** @depends testSearchPictureForPlace */
    public function testAppendPictureToPlace($link)
    {
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('append_remote_picture_append')->form();
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $img = $crawler->filter('.parsed-wikitext .pushable img');
        $this->assertCount(1, $img);
        $this->assertStringContainsString('strawberry', $img->attr('src'));

        $dst = join_paths($this->storage->getRootDir(), PngReaderTest::fixture);
        @unlink($dst);
    }

}
