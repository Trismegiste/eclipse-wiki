<?php

/*
 * eclipse-wiki
 */

use App\Repository\EdgeProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of EdgeProviderTest
 *
 * @author flo
 */
class EdgeProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp():void
    {
        self::createKernel();
        $this->sut = self::getContainer()->get(EdgeProvider::class);
    }

    public function testFindOne()
    {
        $edge = $this->sut->findOne('Biosculpteur');
        var_dump($edge);
    }

}
