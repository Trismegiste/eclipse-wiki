<?php

use App\Entity\LegendSpot;
use App\Entity\Place;
use PHPUnit\Framework\TestCase;

class PlaceTest extends TestCase
{

    protected Place $sut;

    protected function setUp(): void
    {
        $this->sut = new Place('jupiter');
    }

    public function testExtractLegendFromContent()
    {
        $this->sut->setContent("yolo {{legend | redspot | 123 }}  {{legend|storm|456}}");
        $spot = $this->sut->extractLegendSpot();
        $this->assertCount(2, $spot);
        $this->assertInstanceOf(LegendSpot::class, $spot[0]);
        $this->assertInstanceOf(LegendSpot::class, $spot[1]);
        $this->assertEquals('redspot', $spot[0]->getTitle());
        $this->assertEquals('storm', $spot[1]->getTitle());
        $this->assertEquals(123, $spot[0]->getIndex());
        $this->assertEquals(456, $spot[1]->getIndex());
    }

}
