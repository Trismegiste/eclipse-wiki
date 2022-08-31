<?php

/*
 * eclipse-wiki
 */

use App\Voronoi\MapBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MapBuilderTest extends KernelTestCase
{

    protected MapBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(MapBuilder::class);
    }

}
