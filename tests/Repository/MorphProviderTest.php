<?php

/*
 * eclipse-wiki
 */

use App\Repository\MorphProvider;
use App\Service\MediaWiki;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Cache\CacheInterface;

class MorphProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        static::createKernel();
        $this->sut = new MorphProvider(static::getContainer()->get(MediaWiki::class), static::getContainer()->get(CacheInterface::class));
    }

    public function testListing()
    {
        $this->sut->getListing();
    }

}
