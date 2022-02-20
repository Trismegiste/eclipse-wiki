<?php

/*
 * Eclipse Wiki
 */

use App\Entity\Place;
use App\MapLayer\ThumbnailMap;
use App\Repository\MapRepository;
use App\Repository\VertexRepository;
use MongoDB\BSON\ObjectIdInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Trismegiste\MapGenerator\SvgPrintable;

class MapRepositoryTest extends KernelTestCase
{

    /** @var MapRepository sut */
    protected $sut;

    protected function setUp(): void
    {
        self::createKernel();
        $this->sut = static::getContainer()->get(MapRepository::class);
    }

    public function testFindAll()
    {
        $iter = $this->sut->findAll();
        $iter->rewind();
        $first = $iter->current();
        $this->assertInstanceOf(ThumbnailMap::class, $first);

        return $first->getKey();
    }

    /** @depends testFindAll */
    public function testFormParam(string $key)
    {
        $data = $this->sut->getTemplateParam($key);
        $this->assertArrayHasKey('seed', $data);
    }

    public function testWrite()
    {
        $place = new Place('test');

        $svg = $this->createMock(SvgPrintable::class);
        $svg->expects($this->once())
            ->method('printSvg')
            ->willReturnCallback(function() {
                echo '<svg/>';
            });

        $this->sut->writeAndSave($svg, 'toto.svg', $place);
        $this->assertInstanceOf(ObjectIdInterface::class, $place->getPk());

        return $place;
    }

    /** @depends testWrite */
    public function testDeleteDetachedMap(Place $place)
    {
        $repo = static::getContainer()->get(VertexRepository::class);
        $repo->delete($place);

        $this->sut->deleteOrphanMap();
    }

}
