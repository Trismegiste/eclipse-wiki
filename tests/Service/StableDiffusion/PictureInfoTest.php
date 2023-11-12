<?php

/*
 * Eclipse Wiki
 */

use App\Service\StableDiffusion\PictureInfo;
use PHPUnit\Framework\TestCase;

class PictureInfoTest extends TestCase
{

    public function testKeywords()
    {
        $sut = new PictureInfo('file:///aaa.png', '/aaa.png', 128, 'aaa', '(blue-) banana+');
        $this->assertEquals(['blue', 'banana'], $sut->getKeywords());
    }

}
