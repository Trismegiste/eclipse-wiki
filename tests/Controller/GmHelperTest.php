<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GmHelperTest extends WebTestCase
{

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testLoveLetter(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/gm/loveletter');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('love_letter_generate')->form();
        $form->setValues(['love_letter' => [
                "player" => "ABCD",
                "context" => "456",
                "drama" => "789",
                "roll1" => ["trait" => "Agilité", "difficulty" => -1],
                "roll2" => ["trait" => "Agilité", "difficulty" => -1],
                "roll3" => ["trait" => "Agilité", "difficulty" => -1],
                "choice1" => "1",
                "choice2" => "2",
                "choice3" => "3",
                "choice4" => "4"
        ]]);
        $crawler = $client->submit($form);
        $this->assertEquals('Cher ABCD,', $crawler->filterXPath("//p[1]")->text());
    }

    public function testNameGenerate()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/gm/name');
        $this->assertResponseIsSuccessful();
    }

}
