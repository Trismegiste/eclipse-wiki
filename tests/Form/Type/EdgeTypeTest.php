<?php

/*
 * eclipse-wiki
 */

use App\Entity\Edge;
use App\Form\Type\EdgeType;
use App\Repository\EdgeProvider;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class EdgeTypeTest extends TypeTestCase
{

    private $provider;

    protected function setUp(): void
    {
        // mock any dependencies
        $this->provider = $this->createMock(EdgeProvider::class);
        $this->provider->expects($this->any())
                ->method('findOne')
                ->willReturnCallback(function ($name) {
                    return new Edge($name, 'N', 'soc');
                });
        parent::setUp();
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $type = new EdgeType($this->provider);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    public function testEmpty()
    {
        $sut = $this->factory->create(EdgeType::class);
        $sut->submit(['name' => 'yolo']);
        $edge = $sut->getData();
        $this->assertTrue($sut->isSynchronized());
        $this->assertInstanceOf(Edge::class, $edge);
        $this->assertEquals('yolo', $edge->getName());
    }

    public function testEdit()
    {
        $sut = $this->factory->create(EdgeType::class, new Edge('Object', 'N', 'bak'));
        $sut->submit(['name' => 'yaya']);
        $edge = $sut->getData();
        $this->assertTrue($sut->isSynchronized());
        $this->assertInstanceOf(Edge::class, $edge);
        $this->assertEquals('yaya', $edge->getName());
    }

}
