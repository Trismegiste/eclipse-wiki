<?php

/*
 * eclipse-wiki
 */

class BackgroundTest extends PHPUnit\Framework\TestCase
{

    public function testUid()
    {
        $sut = new App\Entity\Background('yolo');
        $this->assertEquals('yolo', $sut->getUId());
        $this->assertEquals('yolo', $sut->title);
    }

}
