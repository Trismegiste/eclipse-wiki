<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

/**
 * Description of ModifierTest
 *
 * @author flo
 */
abstract class ModifierTest extends \PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = $this->create();
    }

    abstract protected function create(string $name = 'yolo'): \App\Entity\Modifier;

    public function testIsEgo()
    {
        $this->assertFalse($this->sut->isEgo());
    }

    public function testIsBio()
    {
        $this->assertFalse($this->sut->isBio());
    }

    public function testIsSynth()
    {
        $this->assertFalse($this->sut->isSynth());
    }

    public function testName()
    {
        $this->assertEquals('yolo', $this->sut->getName());
    }

    public function testUid()
    {
        $this->assertEquals('yolo', $this->sut->getUId());
    }

}
