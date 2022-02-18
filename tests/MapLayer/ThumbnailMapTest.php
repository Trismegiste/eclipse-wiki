<?php

/*
 * eclipse-wiki
 */

use App\MapLayer\ThumbnailMap;
use PHPUnit\Framework\TestCase;

class ThumbnailMapTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $svg = new \SplFileInfo(join_paths(__DIR__, 'map-test.svg'));
        $this->sut = new ThumbnailMap($svg);
    }

    public function testTitle()
    {
        $this->assertEquals('Sample', $this->sut->getTitle());
    }

    public function testRecipe()
    {
        $this->assertEquals("station", $this->sut->getRecipe());
    }

    public function testFormData()
    {
        $data = $this->sut->getFormData();
        $this->assertArrayHasKey('iteration', $data);
        $this->assertEquals(10, $data['iteration']);
    }

}
