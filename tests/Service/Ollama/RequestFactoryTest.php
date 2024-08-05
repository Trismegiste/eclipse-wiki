<?php

use App\Service\Ollama\RequestFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RequestFactoryTest extends KernelTestCase
{

    protected RequestFactory $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(App\Service\Ollama\RequestFactory::class);
    }

    public function testPayload()
    {
        $payload = $this->sut->create('prompt');
        $this->assertTrue($payload->stream);
        $this->assertStringContainsString('mistral', $payload->model);
        $this->assertCount(2, $payload->messages);
        $this->assertEquals('system', $payload->messages[0]->role);
        $this->assertEquals('user', $payload->messages[1]->role);
    }

}
