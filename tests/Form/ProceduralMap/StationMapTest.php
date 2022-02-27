<?php

use App\Form\ProceduralMap\StationMap;
use App\Tests\Form\ProceduralMap\MapRecipeTestCase;
use Trismegiste\MapGenerator\RpgMap;

/*
 * Eclipse Wiki
 */

class StationMapTest extends MapRecipeTestCase
{

    public function testSubmitValidData()
    {
        $formData = [
            'side' => 25,
            'seed' => 666,
            'iteration' => 15,
            'capping' => 5,
            'divide' => 1,
            'npc' => 0,
            'blurry' => true,
            'one_more' => true
        ];

        $form = $this->factory->create(StationMap::class);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf(RpgMap::class, $form->getData());
    }

}
