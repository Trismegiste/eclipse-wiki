<?php

use App\Entity\Artefact;
use App\Entity\Transhuman;
use App\Entity\Place;

class ArtefactTest extends PhpUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp():void
    {
        $this->sut = new Artefact('MacGuffin');
    }

    public function testTranshumanOwner()
    {
        $owner = new Transhuman('yolo', $this->createStub(App\Entity\Background::class), $this->createStub(App\Entity\Faction::class));
        $this->sut->setOwner($owner);
        $this->assertInstanceOf(Transhuman::class, $this->sut->getOwner());
        $this->assertEquals('yolo', $this->sut->getOwner()->getTitle());
    }

    public function testPlaceOwner()
    {
        $place = new Place('Somewhere');
        $this->sut->setOwner($place);
        $this->assertInstanceOf(Place::class, $this->sut->getOwner());
        $this->assertEquals('Somewhere', $this->sut->getOwner()->getTitle());
    }

}
