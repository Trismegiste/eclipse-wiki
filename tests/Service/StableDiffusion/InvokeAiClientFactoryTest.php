<?php

/*
 * eclipse-wiki
 */

use App\Service\StableDiffusion\InvokeAiClient;
use App\Service\StableDiffusion\InvokeAiClientFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InvokeAiClientFactoryTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $client = $this->createStub(HttpClientInterface::class);
        $cache = new ArrayAdapter();
        $this->sut = new InvokeAiClientFactory($client, $cache);
    }

    public function testFromHostname()
    {
        $api = $this->sut->createFromHostname('yolo.mars');
        $this->assertInstanceOf(InvokeAiClient::class, $api);
    }

    public function testFromMac()
    {
        $api = $this->sut->createFromMac('01:02:03:04');
        $this->assertInstanceOf(InvokeAiClient::class, $api);
    }

}
