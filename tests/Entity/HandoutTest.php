<?php

/*
 * eclipse-wiki
 */

use App\Entity\Handout;
use PHPUnit\Framework\TestCase;

class HandoutTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new Handout('tst');
    }

    public function testMongoEntity()
    {
        $this->sut->setContent('valid');
        // no gminfo
        $dump = json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($this->sut)), true);
        $this->assertArrayHasKey('gmInfo', $dump);
        $this->assertNull($dump['gmInfo']);
        // some info here : gmInfo is an optional string, which defaults to null (a.k.a "?string $gmInfo = null" in the class)
        // If the "=null" part is missing, the field is not serialized by mongo driver since this property
        // is undefined (and undefined DOES NOT equal to NULL). The property is simply skipped.
        // Be carefull with full typing in PHP 8 and mongodb driver. Be sure all values are defined to be serialized 
        // or handle the case when some values are missing. This is a reminder for later
    }

}

