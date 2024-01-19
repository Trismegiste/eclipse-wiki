<?php

use App\Repository\VertexRepository;
use App\Tests\Controller\PictureFixture;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
 * eclipse-wiki
 */

class GmPusherTest extends WebTestCase
{

    use PictureFixture;

    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testPeeringUnknownCharacter()
    {
        $crawler = $this->client->request('GET', '/peering');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('QRious', $this->client->getResponse()->getContent());

        // peering
        $form = $crawler->selectButton('peering_confirm_confirm')->form();
        $values = $form->getPhpValues();
        $values['peering_confirm']['pc'] = '1234';
        $values['peering_confirm']['key'] = 666;

        $this->client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
        $this->assertResponseIsSuccessful();
        $rsp = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('error', $rsp->level);
    }

    public function testPeeringValidCharacter()
    {
        $pc = $this->createRandomTranshuman();
        $pc->wildCard = true;
        $repo = static::getContainer()->get(App\Repository\VertexRepository::class);
        $repo->save($pc);

        $crawler = $this->client->request('GET', '/peering');

        // peering in ajax
        $this->client->request('POST', '/peering', ['peering_confirm' => [
                'key' => '6666',
                'pc' => (string) $pc->getPk()
        ]]);

        $this->assertResponseIsSuccessful();
        $rsp = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('success', $rsp->level, 'Invalid form ' . $rsp->message);
        $this->assertEquals(6666, $rsp->remove);
    }

    public function testBadCallOfAjaxPeering()
    {
        $this->client->request('POST', '/peering');
        $this->assertResponseStatusCodeSame(400);
    }

}
