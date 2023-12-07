<?php

/*
 * eclipse-wiki
 */

use App\Service\InfoDashboard;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InfoDashboardTest extends KernelTestCase
{

    protected InfoDashboard $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(InfoDashboard::class);
    }

    public function testVertexCount()
    {
        $this->sut->getVertexCount();
    }

}
