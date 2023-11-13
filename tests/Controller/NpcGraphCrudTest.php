<?php

/*
 * eclipse-wiki
 */

use App\Repository\CreationGraphProvider;
use App\Repository\VertexRepository;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\Strangelove\MongoDb\Repository;

class NpcGraphCrudTest extends WebTestCase
{

    protected KernelBrowser $client;
    protected Repository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testReset()
    {
        $this->repository->delete(iterator_to_array($this->repository->search()));
        $this->assertCount(0, iterator_to_array($this->repository->search()));
        $storage = static::getContainer()->get(Storage::class);
        /** @var Storage $storage */
        $configFile = join_paths($storage->getRootDir(), CreationGraphProvider::FILENAME);
        @unlink($configFile);
        $this->assertFileDoesNotExist($configFile);
    }

    public function testAddingNewNodes()
    {
        $crawler = $this->client->request('GET', '/npc-graph/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#full_tree_save');
        $form = $crawler->selectButton('full_tree_save')->form();

        $values = $form->getPhpValues();
        $values['full_tree']['node'][0]['name'] = 'root';
        $values['full_tree']['node'][1]['name'] = 'male';
        $values['full_tree']['node'][2]['name'] = 'female';

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testLinkingNodes()
    {
        $crawler = $this->client->request('GET', '/npc-graph/edit');
        $this->assertResponseIsSuccessful();
        $elem = $crawler->filter('form.pure-form h2');
        $this->assertCount(3, $elem);
        $this->assertEquals('Root', $elem->eq(0)->text());
        $this->assertEquals('Male', $elem->eq(1)->text());
        $this->assertEquals('Female', $elem->eq(2)->text());

        $form = $crawler->selectButton('full_tree_save')->form();
        $form['full_tree']['node'][0]['children'][1]->tick();
        $form['full_tree']['node'][0]['children'][2]->tick();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testRun()
    {
        $crawler = $this->client->request('GET', '/npc-graph/run');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#selector_generate');
        $form = $crawler->selectButton('selector_generate')->form();

        $values = $form->getPhpValues();
        $values['selector']['title'] = 'Quick NPC';
        $values['selector']['background'] = 'Hilote';
        $values['selector']['faction'] = 'Tamiseur';
        $values['selector']['morph'] = 'Basique';

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteNode()
    {
        $crawler = $this->client->request('GET', '/npc-graph/edit');
        $this->assertResponseIsSuccessful();
        $elem = $crawler->filter('form.pure-form h2')->eq(1);
        $this->assertEquals('Male', $elem->text());
        $link = $elem->filter('a')->link();
        $crawler = $this->client->click($link);
        // delete form
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#delete_node_delete');
        $form = $crawler->selectButton('delete_node_delete')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $elem = $crawler->filter('form.pure-form h2');
        $this->assertCount(2, $elem);
    }

}
