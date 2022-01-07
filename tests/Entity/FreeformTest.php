<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

class FreeformTest extends \PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new \App\Entity\Freeform('sut');
        $morph = new \App\Entity\Morph('Indissociable');
        $morph->type = 'Monstre';
        $this->sut->setMorph($morph);
    }

    public function testDescription()
    {
        $this->assertEquals('Monstre', $this->sut->getDescription());
    }

}
