<?php

/*
 * eclipse-wiki
 */

use App\Repository\EdgeProvider;
use App\Repository\HindranceProvider;
use App\Repository\MorphProvider;
use App\Service\MediaWiki;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class MorphProviderTest extends TestCase
{

    protected $sut;

    protected function buildTypeMorphDocument()
    {
        $doc = new DOMDocument();
        $doc->loadHTML('<ul><li><a href="Catégorie:Biomorphe">Biomorphe</a></li></ul>');

        return $doc;
    }

    protected function buildMorphDocument()
    {
        $doc = new DOMDocument();
        $doc->loadHTML('<div data-source="type"><div>Biomorphe</div></div><div data-source="cout"><div>3</div></div>');

        return $doc;
    }

    protected function buildMorphTree()
    {
        return new DOMDocument();
    }

    protected function setUp(): void
    {
        $api = $this->createMock(MediaWiki::class);
        $api->expects($this->any())
                ->method('getDocumentByName')
                ->willReturnMap([
                    ['Type de Morphe', $this->buildTypeMorphDocument()]
        ]);

        $api->expects($this->any())
                ->method('searchPageFromCategory')
                ->willReturn([(object) ['title' => 'Basique']]);

        $api->expects($this->any())
                ->method('getTreeAndHtmlDomByName')
                ->willReturn(['html' => $this->buildMorphDocument(), 'tree' => $this->buildMorphTree()]);

        $cache = new ArrayAdapter();
        //  $cache = new Symfony\Component\Cache\Adapter\NullAdapter();
        $ep = $this->createStub(EdgeProvider::class);
        $hp = $this->createStub(HindranceProvider::class);

        $this->sut = new MorphProvider($api, $cache, $ep, $hp);
    }

    public function testListing()
    {
        $listing = $this->sut->getListing();
        $this->assertIsArray($listing);
        $this->assertCount(1, $listing);
        $this->assertCount(1, $listing['Biomorphe']);
    }

    public function testFindOne()
    {
        $morph = $this->sut->findOne('Basique');
        $this->assertInstanceOf(\App\Entity\Morph::class, $morph);
    }

}
