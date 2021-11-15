<?php

/*
 * eclipse-wiki
 */

use App\Entity\MeleeWeapon;
use App\Tests\Entity\WeaponTest;

class MeleeWeaponTest extends WeaponTest
{

    protected function setUp(): void
    {
        $this->sut = new MeleeWeapon('club', 'FOR+d6', 2);
    }

    public function testRoF()
    {
        $this->assertEquals(2, $this->sut->ap);
    }

}
