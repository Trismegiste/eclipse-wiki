<?php

use App\Service\MediaWiki;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MediaWikiTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $cl = $this->createMock(HttpClientInterface::class);

        $response = $this->createMock(\Symfony\Contracts\HttpClient\ResponseInterface::class);
        $response->expects($this->any())
                ->method('getStatusCode')
                ->willReturn(200);
        $response->expects($this->any())
                ->method('getContent')
                ->willReturn('{"parse":{"text":{"*":123}}}');

        $cl->expects($this->any())
                ->method('request')
                ->willReturn($response);

        $this->sut = new MediaWiki($cl, 'https://test.tst');
    }

    public function testGetPageByName()
    {
        $ret = $this->sut->getPageByName('yolo');
        $this->assertEquals(123, $ret);
    }

}
