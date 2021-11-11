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

}
