<?php

/*
 * eclipse-wiki
 */

use App\Entity\FighterDecorator;
use App\Entity\Freeform;
use PHPUnit\Framework\TestCase;

class FighterDecoratorTest extends TestCase
{

    /** @dataProvider getCharacter */
    public function testSerialize($wrapped)
    {
        $sut = new FighterDecorator($wrapped);
        $this->assertEquals(array(
            'ranged' => 0,
            'toughness' => 2,
            'parry' => 2,
            'wildcard' => false,
            'title' => 'rancor',
            'armor' => 0,
            'token' => NULL,
                ), $sut->jsonSerialize());
    }

    public function getCharacter(): array
    {
        $obj = new Freeform('rancor');
        $obj->attributes[] = new App\Entity\Attribute('Vigueur');

        return [
            [$obj]
        ];
    }

}
