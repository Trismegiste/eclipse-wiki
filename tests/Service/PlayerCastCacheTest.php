<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\PlayerCastCache;

class PlayerCastCacheTest extends KernelTestCase
{

    protected $sut;

    public function setUp(): void
    {
        $this->sut = static::getContainer()->get(PlayerCastCache::class);
    }

    public function testClearCache()
    {
        $cacheDir = static::$kernel->getCacheDir();
        $this->sut->clear($cacheDir);
        $this->assertTrue(true);
    }

}
