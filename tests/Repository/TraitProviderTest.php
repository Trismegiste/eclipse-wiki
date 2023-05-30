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

    public function testFindAtrributes()
    {
        $attr = $this->sut->findAttributes();
        $this->assertCount(5, $attr);
    }

}
