<?php

/*
 * Eclipse Wiki
 */

use App\Form\ProceduralMap\DistrictMap;
use App\Tests\Form\ProceduralMap\MapRecipeTestCase;
use Trismegiste\MapGenerator\RpgMap;

class DistrictMapTest extends MapRecipeTestCase
{

    public function testSubmitValidData()
    {
        $formData = [
            'sizePerBlock' => 25,
            'blockCount' => 3,
            'seed' => 666,
            'iteration' => 15,
            'capping' => 5,
            'divide' => 1,
            'outsider' => 1,
            'insider' => 1,
            'blurry' => true,
            'one_more' => true
        ];

        $form = $this->factory->create(DistrictMap::class);
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertInstanceOf(RpgMap::class, $form->getData());
    }

}
