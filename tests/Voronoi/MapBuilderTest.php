<?php

/*
 * eclipse-wiki
 */

use App\Entity\MapConfig;
use App\Entity\Shape\NullShape;
use App\Voronoi\HexaMap;
use App\Voronoi\MapBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MapBuilderTest extends KernelTestCase
{

    protected MapBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(MapBuilder::class);
    }

    public function getConfig()
    {
        $config = new MapConfig('essai');
        $config->side = 20;
        $config->seed = 111;
        $config->avgTilePerRoom = 15;
        $config->horizontalLines = 1;
        $config->verticalLines = 1;
        $config->container = new NullShape();

        return [[$config]];
    }

    /** @dataProvider getConfig */
    public function testCreate(MapConfig $config)
    {
        $map = $this->sut->create($config);
        $this->assertInstanceOf(HexaMap::class, $map);
        $this->assertEquals(20, $map->getSize());
    }

}
