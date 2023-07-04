<?php

/*
 * eclipse-wiki
 */

class MorphTest extends PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new App\Entity\Morph('yolo');
    }

    public function testUid()
    {
        $this->assertEquals('yolo', $this->sut->getUId());
    }

    public function testSearchAttributeEmpty()
    {
        $this->assertNull($this->sut->searchAttributeBonus('UNK'));
    }

    public function testSearchSkillEmpty()
    {
        $this->assertNull($this->sut->searchSkillBonus('unknown'));
    }

}
