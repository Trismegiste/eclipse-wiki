<?php

use App\Service\MediaWiki;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaWikiTest extends KernelTestCase
{

    protected MediaWiki $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(MediaWiki::class);
    }

    public function testGetDocumentByName()
    {
        $ret = $this->sut->getDocumentByName('mars');
        $this->assertInstanceOf(DOMDocument::class, $ret);
    }

    public function testGetTemplateData()
    {
        $ret = $this->sut->getTemplateData('SaWoAttribut');
        $this->assertArrayHasKey('agi', $ret);
    }

    public function testRenderTemplate()
    {
        $ret = $this->sut->renderTemplate('SaWoAttribut', 'Attr', ['for' => 6]);
        $this->assertStringContainsString('d6', $ret);
    }

    public function testSearchImage()
    {
        $ret = $this->sut->searchImage('mars');
        $this->assertNotEmpty($ret);

        return $ret;
    }

    /** @depends testSearchImage */
    public function testRenderGallery(array $listing)
    {
        $ret = $this->sut->renderGallery($listing);
        $this->assertStringContainsString('<figure', $ret);

        return $ret;
    }

    /** @depends testRenderGallery */
    public function testExtractUrlGallery(string $gallery)
    {
        $ret = $this->sut->extractUrlFromGallery($gallery);
        $this->assertGreaterThan(0, count($ret));
        $this->assertNotEmpty($ret[0]->thumbnail);
    }

    public function testGetTreeByName()
    {
        $ret = $this->sut->getTreeAndHtmlDomByName('Furie');
        $this->assertIsArray($ret);
        $this->assertInstanceOf(DOMDocument::class, $ret['tree']);
        $this->assertInstanceOf(DOMDocument::class, $ret['html']);
        $this->assertEquals('root', $ret['tree']->firstChild->nodeName);
        $this->assertEquals('html', $ret['html']->firstChild->nodeName);
    }

}
