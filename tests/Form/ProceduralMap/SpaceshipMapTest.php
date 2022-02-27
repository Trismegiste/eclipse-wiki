<?php

/*
 * Eclipse Wiki
 */

use App\Form\ProceduralMap\SpaceshipMap;
use App\Tests\Form\ProceduralMap\MapRecipeTestCase;
use Trismegiste\MapGenerator\RpgMap;

class SpaceshipMapTest extends MapRecipeTestCase
{

    public function testSubmitValidData()
    {
        $formData = [
            'side' => 25,
            'seed' => 666,
            'iteration' => 15,
            'capping' => 5,
            'divide' => 1,
            'npc' => 0
        ];

        $form = $this->factory->create(SpaceshipMap::class);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf(RpgMap::class, $form->getData());
    }

}
