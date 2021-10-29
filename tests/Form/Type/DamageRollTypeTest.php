<?php

/*
 * eclipse-wiki
 */

use App\Form\Type\DamageRollType;
use App\Entity\DamageRoll;

class DamageRollTypeTest extends \Symfony\Component\Form\Test\TypeTestCase
{

    public function getData(): array
    {
        return [
            ['', [4 => 0, 6 => 0, 8 => 0, 10 => 0, 12 => 0], 0],
            ['5', [4 => 0, 6 => 0, 8 => 0, 10 => 0, 12 => 0], 5],
            ['1d4', [4 => 1, 6 => 0, 8 => 0, 10 => 0, 12 => 0], 0],
            ['3d8', [4 => 0, 6 => 0, 8 => 3, 10 => 0, 12 => 0], 0],
            ['3d6+1d4+2', [4 => 1, 6 => 3, 8 => 0, 10 => 0, 12 => 0], 2],
        ];
    }

    /**
     * @dataProvider getData
     */
    public function testDifferentCase(string $data, array $dice, int $bonus)
    {
        $form = $this->factory->create(DamageRollType::class);
        $form->submit($data);
        $this->assertInstanceOf(DamageRoll::class, $form->getData());
        $obj = $form->getData();

        $this->assertEquals($dice, $obj->getDiceCount());
        $this->assertEquals($bonus, $obj->getBonus());
    }

    public function testView()
    {
        $roll = new DamageRoll();
        $roll->addDice(4, 1);

        $form = $this->factory->create(DamageRollType::class, $roll);
        $view = $form->createView();

        $this->assertSame('1d4', $view->vars);
    }

}
