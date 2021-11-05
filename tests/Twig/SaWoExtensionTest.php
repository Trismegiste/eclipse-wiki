<?php

/*
 * eclipse-wiki
 */

use App\Twig\SaWoExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SaWoExtensionTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        static::bootKernel();
        $trans = self::getContainer()->get(TranslatorInterface::class);
        $this->sut = new SaWoExtension($trans);
    }

    public function testDiceIcon()
    {
        $this->assertEquals('<i class="icon-d4"></i>', $this->sut->diceIcon('d4'));
        $this->assertEquals('<i class="icon-d8"></i>', $this->sut->diceIcon('d8'));
        $this->assertEquals('<i class="icon-d12"></i>', $this->sut->diceIcon('d12'));
        $this->assertEquals('<i class="icon-d12"></i>+2', $this->sut->diceIcon('d12+2'));
    }

    public function testAddRaise()
    {
        $roll = new App\Entity\DamageRoll();
        $roll = $this->sut->addRaise($roll);
        $this->assertEquals(1, $roll->getDieCount(6));
        $roll = $this->sut->addRaise($roll);
        $this->assertEquals(2, $roll->getDieCount(6));
    }

}
