<?php

/*
 * eclipse-wiki
 */

use App\Service\MwImageCache;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MwImageCacheTest extends KernelTestCase
{

    protected $sut;

    public function setUp(): void
    {
        $this->sut = static::getContainer()->get(MwImageCache::class);
    }

    public function testClearCache()
    {
        $cacheDir = static::$kernel->getCacheDir();
        $this->sut->clear($cacheDir);
        $this->assertTrue(true);
    }

}
