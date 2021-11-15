<?php

/*
 * eclipse-wiki
 */

class MorphTest extends PHPUnit\Framework\TestCase
{

    public function testUid()
    {
        $sut = new App\Entity\Morph('yolo');
        $this->assertEquals('yolo', $sut->getUId());
    }

}
