<?php

use App\Entity\Artefact;
use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Place;
use App\Entity\Transhuman;
use PHPUnit\Framework\TestCase;

class ArtefactTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new Artefact('MacGuffin');
    }

    public function testTranshumanOwner()
    {
        $owner = new Transhuman('yolo', $this->createStub(Background::class), $this->createStub(Faction::class));
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
