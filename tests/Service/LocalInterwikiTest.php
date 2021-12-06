<?php

/*
 * eclipse-wiki
 */

use App\Service\LocalInterwiki;
use PHPUnit\Framework\TestCase;

class LocalInterwikiTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new LocalInterwiki('essai.test');
    }

    public function testNamespace()
    {
        $this->assertTrue($this->sut->hasNamespace('ep'));
    }

    public function testTargetUrl()
    {
        $this->assertStringStartsWith('http', $this->sut->getTargetUrl('ep'));
    }

}
