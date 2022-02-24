<?php

/*
 * eclipse-wiki
 */

use App\Entity\BtMessage;
use PHPUnit\Framework\TestCase;

class BtMessageTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new BtMessage('12:34:56');
    }

    public function testAddress()
    {
        $this->assertEquals('12:34:56', $this->sut->getMacAddress());
    }

}
