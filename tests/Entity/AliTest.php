<?php

/*
 * eclipse-wiki
 */

class AliTest extends App\Tests\Entity\CharacterTest
{

    public function createCharacter(): \App\Entity\Character
    {
        return new App\Entity\Ali('test');
    }

    public function testDescription()
    {
        $this->assertEquals('IAL', $this->sut->getDescription());
    }

}
