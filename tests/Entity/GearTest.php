<?php

/*
 * eclipse-wiki
 */

class GearTest extends PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new \App\Entity\Gear();
        $this->sut->setName('yolo');
    }

    public function testUid()
    {
        $this->assertEquals('yolo', $this->sut->getUId());
    }

    public function testJson()
    {
        $this->assertJson(json_encode($this->sut));
    }

}
