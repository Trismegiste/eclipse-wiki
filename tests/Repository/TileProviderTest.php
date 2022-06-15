<?php

/*
 * eclipse-wiki
 */

use App\Repository\TileProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TileProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        self::createKernel();
        $this->sut = static::getContainer()->get(TileProvider::class);
    }

    public function testFindAll()
    {
        $iter = $this->sut->findAll();
        $this->assertGreaterThan(1, count($iter));
    }

}
