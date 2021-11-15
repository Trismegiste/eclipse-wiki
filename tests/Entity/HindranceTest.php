<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

use App\Entity\Hindrance;
use App\Entity\Modifier;

class HindranceTest extends ModifierTest
{

    protected function create(string $name = 'yolo'): Modifier
    {
        return new Hindrance($name);
    }

    public function testJson()
    {
        $this->assertJson(json_encode($this->sut));
    }

    public function testChoices()
    {
        $this->assertEquals(Hindrance::MAJOR | Hindrance::MINOR, $this->sut->getChoices());
    }

    public function testLevel()
    {
        $this->sut->setLevel(Hindrance::MAJOR);
        $this->assertEquals(Hindrance::MAJOR, $this->sut->getLevel());
    }

    public function testBadLevel()
    {
        $sut = new Hindrance('limi', false, false, false, Hindrance::MINOR);
        $this->expectException(\UnexpectedValueException::class);
        $sut->setLevel(Hindrance::MAJOR);
    }

}
