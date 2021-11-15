<?php

/*
 * eclipse-wiki
 */

class ArmorTest extends PHPUnit\Framework\TestCase
{

    public function testUid()
    {
        $sut = new \App\Entity\Armor('yolo', 4);
        $this->assertEquals('yolo', $sut->getUId());
        $this->assertEquals(4, $sut->protect);
    }

}
