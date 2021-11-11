<?php

use App\Repository\BackgroundProvider;
use App\Service\MediaWiki;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

/*
 * Eclipse Wiki
 */

/**
 * Description of BackgroundProviderTest
 */
class BackgroundProviderTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $api = $this->createMock(MediaWiki::class);
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn([]);
        $this->sut = new BackgroundProvider($api, $cache);
    }

    public function testListing()
    {
        $this->assertIsArray($this->sut->getListing());
    }

}
