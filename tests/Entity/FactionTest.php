<?php

/*
 * eclipse-wiki
 */

class FactionTest extends PHPUnit\Framework\TestCase
{

    public function testUid()
    {
        $sut = new App\Entity\Faction('yolo');
        $this->assertEquals('yolo', $sut->getUId());
        $this->assertEquals('yolo', $sut->title);
    }

}
