<?php

/*
 * eclipse-wiki
 */

use App\Parsoid\InternalDataAccess;
use App\Parsoid\ParserFactory;
use App\Service\Storage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wikimedia\Parsoid\Parsoid;

class ParserFactoryTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $access = $this->createStub(InternalDataAccess::class);
        $router = $this->createStub(UrlGeneratorInterface::class);
        $storage = $this->createStub(Storage::class);
        $this->sut = new ParserFactory($access, $router, $storage);
    }

    public function provideMode()
    {
        return [
            ['browser'],
            ['pdf']
        ];
    }

    /** @dataProvider provideMode */
    public function testCreation(string $mode)
    {
        $parser = $this->sut->create($mode);
        $this->assertInstanceOf(Parsoid::class, $parser);
    }

    public function testUnknownMode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->create('stone-talet');
    }

}
