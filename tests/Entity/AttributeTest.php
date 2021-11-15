<?php

/*
 * eclipse-wiki
 */

use App\Entity\Attribute;
use App\Entity\SaWoTrait;
use App\Tests\Entity\SaWoTraitTest;

class AttributeTest extends SaWoTraitTest
{

    public function create($name = 'Yolo'): SaWoTrait
    {
        return new Attribute($name);
    }

    public function testJson()
    {
        $this->assertJson(json_encode($this->sut));
    }

}
