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
        libxml_use_internal_errors(true); // because other xml/svg namespace warning
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->loadHTMLFile(dirname(__DIR__) . '/fixtures/fury.html');

        return $doc;
    }

    protected function buildMorphTree()
    {
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->load(dirname(__DIR__) . '/fixtures/fury.xml');

        return $doc;
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
                ->willReturn([(object) ['title' => 'Furie']]);

        $api->expects($this->any())
                ->method('getTreeAndHtmlDomByName')
                ->willReturn(['html' => $this->buildMorphDocument(), 'tree' => $this->buildMorphTree()]);

        $cache = new ArrayAdapter();

        $ep = $this->createMock(EdgeProvider::class);
        $ep->expects($this->any())
                ->method('findOne')
                ->willReturn(new App\Entity\Edge('ETest', 'N', 'Pro'));

        $hp = $this->createMock(HindranceProvider::class);
        $hp->expects($this->any())
                ->method('findOne')
                ->willReturn(new App\Entity\Hindrance('HTest'));

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
        $morph = $this->sut->findOne('Furie');
        $this->assertInstanceOf(\App\Entity\Morph::class, $morph);
        $this->assertCount(3, $morph->getEdges());
        $this->assertCount(1, $morph->getHindrances());
        $this->assertCount(2, $morph->attributeBonus);
        $this->assertCount(3, $morph->skillBonus);
        $this->assertEquals(4, $morph->bodyArmor);
    }

}
