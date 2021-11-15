<?php

/*
 * eclipse-wiki
 */

use App\Entity\SaWoTrait;
use App\Entity\Skill;
use App\Tests\Entity\SaWoTraitTest;

class SkillTest extends SaWoTraitTest
{

    public function create($name = 'Yolo'): SaWoTrait
    {
        return new Skill($name, 'DUMMY');
    }

    public function testJson()
    {
        $this->assertJson(json_encode($this->sut));
    }

}
