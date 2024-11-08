<?php

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Morph;
use App\Repository\CharacterFactory;
use App\Repository\VertexRepository;
use App\Tests\Controller\PictureFixture;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OllamaTest extends WebTestCase
{

    use PictureFixture;

    protected KernelBrowser $client;
    protected VertexRepository $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(VertexRepository::class);
    }

    public function testNpcBackground()
    {
        $factory = static::getContainer()->get(CharacterFactory::class);
        $npc = $factory->create('takeshi' . rand(), new Background('bg'), new Faction('diplo'));
        $npc->setContent('information');
        $npc->setMorph(new Morph('dummy'));
        $this->repository->save($npc);

        $pk = $npc->getPk();
        $crawler = $this->client->request('GET', "/npc/info/$pk");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#npc_info_create');
        $button = $crawler->selectButton('npc_info_create')
                ->ancestors()
                ->filter('form')
                ->filter('i[class=icon-background]'); // just to be sure we select the background icon inside the minitoolbar inside the edit form
        $this->assertCount(1, $button);

        $crawler = $this->client->click($button->ancestors()->link());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#npc_background_generate');
        $this->assertSelectorNotExists('#llm_output_append_save');
        $this->assertFormValue('form[name=npc_background]', 'npc_background[title]', $npc->getTitle());
        $form = $crawler->selectButton('npc_background_generate')->form();

        $form->setValues(['npc_background' => ['role' => 'diplo']]);
        $crawler = $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#llm_output_append_save');
        $form = $crawler->selectButton('llm_output_append_save')->form();
        $this->assertStringStartsNotWith($form->getValues()['llm_output_append[prompt_query]'], 'Dans le contexte');

        $form->setValues(['llm_output_append' => ['generation' => 'Harlan']]);
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('main .parsed-wikitext', 'Harlan');
    }

    public function testBar()
    {
        $v = $this->createRandomPlace();
        $this->repository->save($v);
        $pk = $v->getPk();
        $this->client->request('GET', "/ollama/vertex/$pk/generate/bar");
        $this->assertResponseIsSuccessful();
    }

    public function getListingKey()
    {
        return [
            ['npc-name'],
            ['thing-name'],
        ];
    }

    /** @dataProvider getListingKey */
    public function testListingGeneration(string $key)
    {
        $this->client->request('GET', "/ollama/creation/listing/$key");
        $this->assertResponseIsSuccessful();
    }

}
