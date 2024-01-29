<?php

/*
 * eclipse-wiki
 */

use App\Service\FileIoClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileIoClientTest extends WebTestCase
{

    public function testUpload()
    {
        $client = self::getContainer()->get(FileIoClient::class);
        $resp = $client->upload(new SplFileInfo(__DIR__ . '/profilepic.png'));
        $this->assertStringStartsWith('https://file.io/', $resp);
    }

}
