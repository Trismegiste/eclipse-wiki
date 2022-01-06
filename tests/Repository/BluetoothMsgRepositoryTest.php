<?php

/*
 * eclipse-wiki
 */

use App\Repository\BluetoothMsgRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BluetoothMsgRepositoryTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        static::createKernel();
        $this->sut = static::getContainer()->get(BluetoothMsgRepository::class);
    }

    public function testReset()
    {
        $this->sut->reset();
        $this->assertCount(0, $this->sut->search());
    }

    public function testTailableCursor()
    {
        $it = $this->sut->getTailableCursor();
        $this->assertInstanceOf(Iterator::class, $it);
    }

}
