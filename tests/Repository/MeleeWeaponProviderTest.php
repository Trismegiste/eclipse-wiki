<?php

/*
 * Eclipse Wiki
 */

use App\Repository\MeleeWeaponProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of MeleeWeaponProviderTest
 */
class MeleeWeaponProviderTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        self::bootKernel();
        $pageRepo = self::getContainer()->get('app.mwpage.repository');
        $this->sut = new MeleeWeaponProvider($pageRepo);
    }

    public function testFindAll()
    {
        $weapons = $this->sut->getListing();
        var_dump($weapons);
    }

}
