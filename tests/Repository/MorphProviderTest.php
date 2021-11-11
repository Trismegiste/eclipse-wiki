<?php

/*
 * eclipse-wiki
 */

use App\Repository\MorphProvider;
use App\Service\MediaWiki;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

class MorphProviderTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $api = $this->createMock(MediaWiki::class);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn([]);
        $this->sut = new MorphProvider($api, $cache);
    }

    public function testListing()
    {
        $this->assertIsArray($this->sut->getListing());
    }

}
