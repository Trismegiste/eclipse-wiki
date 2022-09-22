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

    /** @dataProvider getConfig */
    public function testSvg(MapConfig $config)
    {
        $map = $this->sut->create($config);
        ob_start();
        $this->sut->dumpSvg($map);
        $svg = ob_get_clean();
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
    }

    /** @dataProvider getConfig */
    public function testSaveFile(MapConfig $config)
    {
        $map = $this->sut->create($config);
        $pathname = __DIR__ . '/yolo.svg';

        @unlink($pathname);
        $this->assertFileDoesNotExist($pathname);
        $this->sut->save($map, $pathname);
        $this->assertFileExists($pathname);
        $this->assertFileIsReadable($pathname);
        unlink($pathname);
    }

    public function testDumpingSingleToken()
    {
        $tokenPng = imagecreatetruecolor(100, 100);
        $pathname = __DIR__ . '/token.png';
        @unlink($pathname);
        imagepng($tokenPng, $pathname);
        $info = new \SplFileInfo($pathname);

        ob_start();
        $this->sut->dumpTokenFor($info);
        $dumped = ob_get_clean();
        $this->assertStringContainsString('circle', $dumped);
        $this->assertStringContainsString('image', $dumped);
        $this->assertStringContainsString('xlink:href="data:image/png;base64,', $dumped);
        @unlink($pathname);
    }

}
