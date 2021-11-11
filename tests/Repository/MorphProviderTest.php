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

    public function _testListing()
    {
        var_dump($this->sut->getListing());
    }

    public function _testFindOne()
    {
        var_dump($this->sut->findOne('Nuéenoïde'));
    }

}
