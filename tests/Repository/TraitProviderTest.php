<?php

/*
 * Eclipse Wiki
 */

namespace App\Tests\Repository;

use App\Repository\TraitProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TraitProviderTest extends KernelTestCase
{

    protected TraitProvider $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(TraitProvider::class);
    }

    public function testAtrributes()
    {
        $attr = $this->sut->findAttributes();
        $this->assertCount(5, $attr);
    }

    public function testSkills()
    {
        $listing = $this->sut->findSkills();
        $this->assertCount(27, $listing);
    }

    public function testSocNet()
    {
        $listing = $this->sut->findSocialNetworks();
        $this->assertCount(7, $listing);
    }

}
