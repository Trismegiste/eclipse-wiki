<?php

/*
 * Eclipse Wiki
 */

use App\Form\ProceduralMap\StreetMap;
use App\Tests\Form\ProceduralMap\MapRecipeTestCase;
use Trismegiste\MapGenerator\RpgMap;

class StreetMapTest extends MapRecipeTestCase
{

    public function testSubmitValidData()
    {
        $formData = [
            'streetWidth' => 25,
            'streetCount' => 3,
            'seed' => 666,
            'iteration' => 15,
            'capping' => 5,
            'divide' => 1,
            'npc' => 0,
            'blurry' => true,
            'one_more' => true
        ];

        $form = $this->factory->create(StreetMap::class);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf(RpgMap::class, $form->getData());
    }

}
