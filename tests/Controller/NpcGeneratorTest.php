<?php

namespace App\Tests\Controller;

use App\Entity\Ali;
use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Freeform;
use App\Entity\Transhuman;
use App\Repository\CharacterFactory;
use App\Repository\VertexRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NpcGeneratorTest extends WebTestCase
{

    use PictureFixture;

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testClean()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->delete(iterator_to_array($repo->search()));
        $this->assertCount(0, iterator_to_array($repo->search()));
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/npc/create');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('npc_generate')->form();
        $form->setValues(['npc' => [
                'title' => 'Luke',
                'background' => 'Hilote',
                'faction' => 'Tamiseur',
                'morph' => 'Basique'
        ]]);
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', '/vertex/filter');
        $this->assertResponseIsSuccessful();
        $url = $crawler->filterXPath('//nav/a/i[@class="icon-eye"]/parent::a')->attr('href');

        $this->assertStringContainsString('show', $url);

        return $url;
    }

    /**
     * @depends testList
     */
    public function testShow(string $url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertPageTitleContains('Luke');
        $url = $crawler->filterXPath('//a/i[@class="icon-d6"]/parent::a')->attr('href');

        return $url;
    }

    /**
     * @depends testShow
     */
    public function testEdit(string $url)
    {
        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    public function testSearch()
    {
        $this->client->request('GET', '/vertex/search?q=Lu');
        $listing = json_decode($this->client->getResponse()->getContent());
        $this->assertCount(1, $listing);

        return $listing[0];
    }

    public function testAjaxBackground()
    {
        $this->client->request('GET', "/npc/background/info?key=Hilote");
        $this->assertResponseIsSuccessful();
    }

    public function testAjaxFaction()
    {
        $this->client->request('GET', "/npc/faction/info?key=Tamiseur");
        $this->assertResponseIsSuccessful();
    }

    public function testAjaxMorph()
    {
        $this->client->request('GET', "/npc/morph/info?key=Basique");
        $this->assertResponseIsSuccessful();
    }

    /**
     * @depends testShow
     */
    public function testInfo(string $url): string
    {
        $crawler = $this->client->request('GET', $url);
        $url = $crawler->filterXPath('//a/i[@class="icon-edit"]/parent::a')->attr('href');
        $crawler = $this->client->request('GET', $url);
        $this->assertSelectorExists('#npc_info_create');
        $pk = $this->client->getRequest()->attributes->get('pk');

        $form = $crawler->selectButton('npc_info_create')->form();
        $this->client->submit($form, [
            'npc_info' => [
                'content' => 'some text',
                'surnameLang' => 'french'
            ]
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        return $pk;
    }

    /** @depends testInfo */
    public function testDelete(string $pk)
    {
        $crawler = $this->client->request('GET', "/vertex/delete/$pk");
        $this->assertCount(1, $crawler->selectButton('form_delete'));
    }

    /** @depends testInfo */
    public function testBattle(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/battle/$pk");
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('npc_attacks_edit')->form();
        $this->client->submit($form);
    }

    /** @depends testInfo */
    public function testDuplicate(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/duplicate/$pk");
        $button = $crawler->selectButton('form_copy');
        $this->assertCount(1, $button);

        $form = $button->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
    }

    /** @depends testInfo */
    public function testGear(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/gear/$pk");
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('npc_gears_edit')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testCreateALI()
    {
        $crawler = $this->client->request('GET', '/npc/ali');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('ali_generate')->form();
        $this->client->submit($form, ['ali' => [
                'title' => 'New ALI',
                'morph' => 'Affreux'
        ]]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    /** @depends testInfo */
    public function testSleeve(string $pk)
    {
        $crawler = $this->client->request('GET', "/npc/sleeve/$pk");
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('form_sleeve');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testCreateFreeform()
    {
        $this->client->request('GET', '/npc/freeform');
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('freeform_create_create', ['freeform_create' => [
                'title' => 'New monster',
                'type' => 'Monstre'
        ]]);
        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        return $crawler->getUri();
    }

    /**
     * @depends testCreateFreeform
     */
    public function testEditSubmitWithFreeform(string $edit)
    {
        $this->client->request('GET', $edit);
        $this->assertResponseIsSuccessful();
        $this->client->submitForm('npc_stats_edit');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function getCharacterFqcn()
    {
        return [
            [Transhuman::class],
            [Ali::class],
            [Freeform::class]
        ];
    }

    /** @dataProvider getCharacterFqcn */
    public function testMinicardForCharacter($fqcn)
    {
        $iter = static::getContainer()->get(VertexRepository::class)->findByClass([$fqcn]);
        $iter->rewind();
        $this->client->request('GET', "/npc/minicard?title=" . $iter->current()->getTitle());
        $this->assertResponseIsSuccessful();
        $dump = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $dump);
    }

    public function testMinicardForCharacterTemplate()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $iter = $repo->findByClass(Transhuman::class);
        $iter->rewind();
        $npc = $iter->current();
        $npc->surnameLang = 'japanese';
        $repo->save($npc);

        $this->client->request('GET', "/npc/minicard?title=" . $npc->getTitle());
        $this->assertResponseIsSuccessful();
        $dump = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('instantiate', $dump);
        $this->assertNotNull($dump['instantiate']);
    }

    public function testPictureInMinicard()
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $npc = $this->createRandomTranshuman();
        $npc->setContent("[[file:pp.jpg]]");
        $repo->save($npc);

        $this->client->request('GET', "/npc/minicard?title=" . $npc->getTitle());
        $this->assertResponseIsSuccessful();
        $dump = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $dump);
        $this->assertEquals($npc->getTitle(), $dump['title']);
        $this->assertArrayHasKey('picture', $dump);
        $this->assertCount(1, $dump['picture']);
    }

    public function testGetUnknownNpcJson()
    {
        $this->client->request('GET', "/npc/show.json?title=sjfhsqlfdh");
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetNpcJson()
    {
        $this->client->request('GET', "/npc/show.json?title=Luke");
        $this->assertResponseIsSuccessful();
        $npc = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('Luke', $npc->title);
    }

    public function testResync()
    {
        $fac = static::getContainer()->get(CharacterFactory::class);
        $template = $fac->create('Generic', new Background('dummy'), new Faction('dummy'));
        $template->setMorph(new \App\Entity\Morph('morph'));
        $repo = static::getContainer()->get(VertexRepository::class);
        $template->surnameLang = 'japanese';
        $repo->save($template);

        // instantiate from template
        $crawler = $this->client->request('GET', '/profile/template/' . $template->getPk());
        $this->assertSelectorExists('#profile_on_the_fly_instantiate_npc');
        $form = $crawler->selectButton('profile_on_the_fly_instantiate_npc')->form();
        $form['profile_on_the_fly[title]'] = 'Instance of Generic';
        $filename = 'tmp.png';
        $image = $this->createTestChart(256);
        imagepng($image, $filename);
        $form['profile_on_the_fly[avatar]']->upload($filename);
        $this->client->submit($form);
        unlink($filename);

        // new NPC
        $this->assertResponseRedirects();
        $this->assertStringStartsWith('/vertex/show', $this->client->getResponse()->headers->get('location'));
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Resync
        $newPk = $this->client->getRequest()->attributes->get('pk');
        $crawler = $this->client->request('GET', '/npc/resync/' . $newPk);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#npc_resync_synchronize');
        $form = $crawler->selectButton('npc_resync_synchronize')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    /**
     * @depends testInfo
     */
    public function testPushPdf(string $pk)
    {
        $this->client->request('GET', "/npc/push-sheet/$pk");
        $this->assertResponseRedirects();
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('main .big-link a .icon-export');

        $doc = $crawler->filter('main .big-link a .icon-export')
                ->ancestors()
                ->first()
                ->link();
        $this->client->click($doc);
        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('content-type'));
    }

}
