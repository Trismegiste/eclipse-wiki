<?php

/*
 * Eclipse Wiki
 */

use App\Repository\ArmorProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of ArmorProviderTest
 */
class ArmorProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        self::bootKernel();
        $pageRepo = self::getContainer()->get('app.mwpage.repository');
        $this->sut = new ArmorProvider($pageRepo);
    }

    public function testFindAll()
    {
        $weapons = $this->sut->getListing();
        var_dump($weapons);
    }

}
