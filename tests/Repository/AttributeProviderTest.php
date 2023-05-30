<?php

/*
 * Eclipse Wiki
 */

use App\Repository\AttributeProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AttributeProviderTest extends KernelTestCase
{

    protected AttributeProvider $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(AttributeProvider::class);
    }

    public function testListing()
    {
        $listing = $this->sut->getListing();
        $this->assertCount(5, $listing);
    }

}
