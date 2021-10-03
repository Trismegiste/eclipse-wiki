<?php

use App\Repository\BackgroundProvider;
use App\Service\MediaWiki;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Cache\CacheInterface;

/*
 * Eclipse Wiki
 */

/**
 * Description of BackgroundProviderTest
 */
class BackgroundProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        static::createKernel();
        $this->sut = new BackgroundProvider(static::getContainer()->get(MediaWiki::class), static::getContainer()->get(CacheInterface::class));
    }

    public function testFindOne()
    {
        var_dump($this->sut->findOne('Néo-octopus'));
    }

    public function testListing()
    {
        var_dump($this->sut->getListing());
    }

}
