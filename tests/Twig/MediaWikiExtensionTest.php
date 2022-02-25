<?php

/*
 * eclipse-wiki
 */

use App\Service\MediaWiki;
use App\Twig\MediaWikiExtension;
use PHPUnit\Framework\TestCase;

class MediaWikiExtensionTest extends TestCase
{

    protected $sut;
    protected $api;

    protected function setUp(): void
    {
        $this->api = $this->createMock(MediaWiki::class);
        $this->sut = new MediaWikiExtension($this->api, 'test.yolo');
    }

    public function testGetFunctions()
    {
        $this->assertIsArray($this->sut->getFunctions());
    }

    public function testDumpCategory()
    {
        $this->api->expects($this->once())
                ->method('searchPageFromCategory')
                ->willReturn([(object) ['pageid' => 123, 'title' => 'Dummy']]);

        $haystack = $this->sut->dumpCategory('Something', 1);
        $this->assertStringContainsString('<article>', $haystack);
        $this->assertStringContainsString('<h1>Dummy', $haystack);
    }

    public function testDumpPage()
    {
        $haystack = $this->sut->dumpPage('Nanofab');
        $this->assertStringContainsString('<article>', $haystack);
    }

    public function testExternalWikiLink()
    {
        $this->assertStringStartsWith('http', $this->sut->externalWikiLink('essai'));
    }

}
