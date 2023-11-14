<?php

/*
 * Eclipse Wiki
 */

namespace App\Tests\Service\StableDiffusion;

use App\Service\StableDiffusion\PngReader;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use function join_paths;

class PngReaderTest extends TestCase
{

    const fixture = '9963749d-7da3-4ac8-897b-9f4dcee60fcd.png';

    protected $sut;

    protected function setUp(): void
    {
        $folder = __DIR__ . '/../../fixtures';
        $img = join_paths($folder, self::fixture);
        $this->sut = new PngReader(new SplFileInfo($img));
    }

    public function testImageHasChunk()
    {
        $this->assertTrue($this->sut->hasChunk('tEXt'));
    }

    public function testListChunks()
    {
        $this->assertContains('tEXt', $this->sut->getChunkTypes());
    }

    public function testTextChunk()
    {
        $this->assertNotEmpty($this->sut->getTextChunk());
    }

}
