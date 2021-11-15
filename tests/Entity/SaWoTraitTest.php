<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

abstract class SaWoTraitTest extends \PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = $this->create();
    }

    abstract function create($name = 'Yolo'): \App\Entity\SaWoTrait;

    public function testName()
    {
        $this->assertEquals('Yolo', $this->sut->getName());
    }

    public function testUid()
    {
        $this->assertEquals('Yolo', $this->sut->getUId());
    }

}
