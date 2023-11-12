<?php

/*
 * Eclipse Wiki
 */

use App\Service\StableDiffusion\InvokeAiReader;
use PHPUnit\Framework\TestCase;

class InvokeAiReaderTest extends TestCase
{

    public function testExtractMetadata()
    {
        $folder = __DIR__ . '/../../fixtures';
        $img = join_paths($folder, PngReaderTest::fixture);
        $sut = new InvokeAiReader(new SplFileInfo($img));

        $this->assertTrue($sut->hasChunk('tEXt'));
        $this->assertArrayHasKey('invokeai_metadata', $sut->getTextChunk());
        $this->assertEquals('strawberry', $sut->getPositivePrompt());
        $this->assertEquals(128, $sut->getWidth());
    }

}
