<?php

/*
 * Eclipse Wiki
 */

use App\Service\StableDiffusion\PngReader;
use PHPUnit\Framework\TestCase;

class PngReaderTest extends TestCase
{

    const fixture = '9963749d-7da3-4ac8-897b-9f4dcee60fcd.png';

    public function testExtractMetadata()
    {
        $folder = __DIR__ . '/../../fixtures';
        $img = join_paths($folder, self::fixture);
        $sut = new PngReader(new SplFileInfo($img));

        $this->assertTrue($sut->hasChunk('tEXt'));
        $this->assertNotEmpty($sut->getTextChunk());
    }

}
