<?php

/*
 * Eclipse Wiki
 */

use App\Entity\Vertex;
use PHPUnit\Framework\TestCase;

/**
 * unit test for Vertex
 */
class VertexTest extends TestCase
{

    protected Vertex $sut;

    protected function setUp(): void
    {
        $this->sut = $this->getMockForAbstractClass(Vertex::class, ['yolo']);
    }

    public function testTitle()
    {
        $this->assertEquals('Yolo', $this->sut->getTitle());
    }

    public function testEmptyContent()
    {
        $this->assertNull($this->sut->getContent());
        $this->assertEquals([], $this->sut->getInternalLink());
    }

    public function testContent()
    {
        $this->sut->setContent('dummy');
        $this->assertEquals('dummy', $this->sut->getContent());
    }

    public function testCategory()
    {
        $this->assertStringStartsWith('mock', $this->sut->getCategory());
    }

    public function testExtractPicture()
    {
        $this->sut->setContent('aaaa [[link-avatar.jpg]] [[file:image.jpg]] [[file:avatar.jpg]] end');
        $this->assertCount(2, $this->sut->extractPicture());
        $this->assertEquals(['image.jpg', 'avatar.jpg'], $this->sut->extractPicture());
    }

    public function testExtractFirstPictureEmpty()
    {
        $this->assertNull($this->sut->extractFirstPicture());
    }

    public function testExtractFirstPicture()
    {
        $this->sut->setContent('aaaa [[link-avatar.jpg]] [[file:image.jpg]] [[file:avatar.jpg]] end');
        $this->assertEquals('image.jpg', $this->sut->extractFirstPicture());
    }

    public function testArchived()
    {
        $this->assertFalse($this->sut->getArchived());
        $this->sut->setArchived(true);
        $this->assertTrue($this->sut->getArchived());
    }

    public function testLinkExtractor()
    {
        $str = 'voila [[essai]] rien [[arf|link]] [[file:xxx]] [[ep:ext]]';
        $this->sut->setContent($str);
        $this->assertEquals(['essai', 'arf'], $this->sut->getInternalLink());
    }

    public function testRenameLink()
    {
        $this->sut->setContent('[[épisseur]]');
        $this->sut->renameInternalLink('Épisseur', 'ülyss');
        $this->assertEquals('[[ülyss]]', $this->sut->getContent());
    }

    public function testRenameLinkWithAlias()
    {
        $this->sut->setContent('[[épisseur|morphe]]');
        $this->sut->renameInternalLink('Épisseur', 'ülyss');
        $this->assertEquals('[[ülyss|morphe]]', $this->sut->getContent());
    }

    public function getTitleSample()
    {
        return [
            ['àlèd', 'Àlèd'],
            ['œil perçant', 'Œil perçant'],
            ['çaça', 'Çaça']
        ];
    }

    /**
     * @dataProvider getTitleSample
     */
    public function testSetTitle($title, $result)
    {
        $this->sut->setTitle($title);
        $this->assertEquals($result, $this->sut->getTitle());
    }

}
