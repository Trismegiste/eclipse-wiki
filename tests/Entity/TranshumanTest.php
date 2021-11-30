<?php

/*
 * eclipse-wiki
 */

class TranshumanTest extends App\Tests\Entity\CharacterTest
{

    public function createCharacter(): \App\Entity\Character
    {
        $bg = $this->createStub(\App\Entity\Background::class);
        $fac = $this->createStub(App\Entity\Faction::class);

        return new App\Entity\Transhuman('test', $bg, $fac);
    }

    public function testProperties()
    {
        $this->assertInstanceOf(MongoDB\BSON\Persistable::class, $this->sut->getBackground());
        $this->assertInstanceOf(MongoDB\BSON\Persistable::class, $this->sut->getFaction());
    }

    public function testDescription()
    {
        $this->assertEquals(' - ', $this->sut->getDescription());
    }

}
