<?php

/*
 * eclipse-wiki
 */

use App\Entity\Attribute;
use App\Entity\FighterJsonDecorator;
use App\Entity\Freeform;
use PHPUnit\Framework\TestCase;

class FighterJsonDecoratorTest extends TestCase
{

    /** @dataProvider getCharacter */
    public function testSerialize($wrapped)
    {
        $sut = new FighterJsonDecorator($wrapped);
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
        $obj->attributes[] = new Attribute('Vigueur');

        return [
            [$obj]
        ];
    }

}
