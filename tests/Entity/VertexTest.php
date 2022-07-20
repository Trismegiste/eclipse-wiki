<?php

/*
 * Eclipse Wiki
 */

/**
 * unit test for Vertex
 */
class VertexTest extends PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new App\Entity\Vertex('yolo');
    }

    public function testTitle()
    {
        $this->assertEquals('yolo', $this->sut->getTitle());
    }

    public function testEmptyContent()
    {
        $this->assertNull($this->sut->getContent());
    }

    public function testContent()
    {
        $this->sut->setContent('dummy');
        $this->assertEquals('dummy', $this->sut->getContent());
    }

    public function testCategory()
    {
        $this->assertEquals('vertex', $this->sut->getCategory());
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
}
