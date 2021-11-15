<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

use App\Entity\Edge;
use App\Entity\Modifier;

class EdgeTest extends ModifierTest
{

    protected function create(string $name = 'yolo'): Modifier
    {
        return new Edge($name, 'N', 'cat');
    }

    public function testCategory()
    {
        $this->assertEquals('cat', $this->sut->getCategory());
    }

    public function testRank()
    {
        $this->assertEquals('N', $this->sut->getRank());
    }

    public function testRequis()
    {
        $this->assertEquals('', $this->sut->getPrerequisite());
    }

    public function testJson()
    {
        $this->assertJson(json_encode($this->sut));
    }

}
