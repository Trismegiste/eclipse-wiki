<?php

/*
 * Eclipse Wiki
 */

namespace App\Tests\Entity;

/**
 * Description of MediaWikiPageTest
 *
 * @author florent
 */
class MediaWikiPageTest extends \PHPUnit\Framework\TestCase
{

    protected \App\Entity\MediaWikiPage $sut;

    protected function setUp(): void
    {
        $this->sut = new \App\Entity\MediaWikiPage('biocore', 'morphe');
    }

    public function testInit()
    {
        $this->assertEquals('biocore', $this->sut->getTitle());
        $this->assertEquals('morphe', $this->sut->getCategory());
    }

    public function testPurify()
    {
        $this->sut->content = 'z{{Infobox|yolo}}a{{coucou}}[[Catégorie:morphe]]b[[Fichier:image.png]]c[[Link]]d[[Link|libellé]]rien';
        $this->assertEquals("zabc'''Link'''d'''libellé'''rien", $this->sut->getPurifiedContentForLocalRendering());
    }

}
