<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

use App\Entity\Weapon;
use PHPUnit\Framework\TestCase;

/**
 * Description of WeaponTest
 *
 * @author flo
 */
class WeaponTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new Weapon('club', 'FOR+d6', 2, 1);
    }

    public function testJson()
    {
        $dump = $this->sut->jsonSerialize();
        $this->assertEquals('1d6', $dump['damage']);
    }

    public function testUid()
    {
        $this->assertEquals('club', $this->sut->name);
        $this->assertEquals('club', $this->sut->getUId());
    }

}
