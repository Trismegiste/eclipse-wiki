<?php

/*
 * eclipse-wiki
 */

use App\Entity\RangedWeapon;
use App\Tests\Entity\WeaponTest;

class RangedWeaponTest extends WeaponTest
{

    protected function setUp(): void
    {
        $this->sut = new RangedWeapon('club', 'FOR+d6', 2, 3, '1/2/4', 1);
    }

    public function testRoF()
    {
        $this->assertEquals(3, $this->sut->rof);
    }

}
