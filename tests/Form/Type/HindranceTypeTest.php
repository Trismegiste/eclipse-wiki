<?php

/*
 * eclipse-wiki
 */

use App\Entity\Hindrance;
use App\Form\Type\HindranceType;
use App\Repository\HindranceProvider;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class HindranceTypeTest extends TypeTestCase
{

    private $provider;

    protected function setUp(): void
    {
        // mock any dependencies
        $this->provider = $this->createMock(HindranceProvider::class);
        $this->provider->expects($this->any())
                ->method('findOne')
                ->willReturnCallback(function ($name) {
                    return new Hindrance($name);
                });
        parent::setUp();
    }

    protected function getExtensions()
    {
        // create a type instance with the mocked dependencies
        $type = new HindranceType($this->provider);

        return [
            // register the type instances with the PreloadedExtension
            new PreloadedExtension([$type], []),
        ];
    }

    public function testEmpty()
    {
        $sut = $this->factory->create(HindranceType::class);
        $sut->submit(['name' => 'yolo', 'level' => 1]);
        $hind = $sut->getData();
        $this->assertTrue($sut->isSynchronized());
        $this->assertInstanceOf(Hindrance::class, $hind);
        $this->assertEquals('yolo', $hind->getName());
    }

    public function testEdit()
    {
        $obj = new Hindrance('Object');
        $obj->setLevel(2);
        $sut = $this->factory->create(HindranceType::class, $obj);
        $sut->submit(['name' => 'yaya', 'level' => 1]);
        $hind = $sut->getData();
        $this->assertTrue($sut->isSynchronized());
        $this->assertInstanceOf(Hindrance::class, $hind);
        $this->assertEquals('yaya', $hind->getName());
        $this->assertEquals(1, $hind->getLevel());
    }

}
