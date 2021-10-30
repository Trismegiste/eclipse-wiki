<?php

/*
 * Eclipse Wiki
 */

use App\Repository\RangedWeaponProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of RangedWeaponProviderTest
 */
class RangedWeaponProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        self::bootKernel();
        $pageRepo = self::getContainer()->get('app.mwpage.repository');
        $this->sut = new RangedWeaponProvider($pageRepo);
    }

    public function testFindAll()
    {
        $weapons = $this->sut->getListing();
        var_dump($weapons);
    }

}
