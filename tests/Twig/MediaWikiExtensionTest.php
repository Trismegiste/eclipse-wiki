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

    protected function setUp(): void
    {
        $this->sut = new MediaWikiExtension($this->createStub(MediaWiki::class), 'test.yolo');
    }

    public function testGetFunctions()
    {
        $this->assertIsArray($this->sut->getFunctions());
    }

}
