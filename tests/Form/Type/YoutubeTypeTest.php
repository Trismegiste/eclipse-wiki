<?php

/*
 * eclipse-wiki
 */

use App\Form\Type\YoutubeType;
use Symfony\Component\Form\Test\TypeTestCase;

class YoutubeTypeTest extends TypeTestCase
{

    protected $sut;

    public function validUrl(): array
    {
        return [
            ['01234567890', '01234567890'],
            ['https://youtu.be/NiNTrKsQ8TU', 'NiNTrKsQ8TU'],
            ['https://youtube.com/watch?v=0123456789-', '0123456789-'],
            ['https://www.youtube.com/watch?v=0123456789a', '0123456789a'],
            ['https://www.youtube.com/watch?v=PQlhC0lEKzw&ab_channel=bestbass42', 'PQlhC0lEKzw']
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sut = $this->factory->create(YoutubeType::class);
    }

    public function testEmpty()
    {
        $this->sut->submit('   ');
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertNull($this->sut->getData());
    }

    /** @dataProvider validUrl */
    public function testValid(string $url, string $id)
    {
        $this->sut->submit($url);
        $this->assertTrue($this->sut->isSynchronized());
        $this->assertEquals($id, $this->sut->getData());
    }

    public function testInvalid()
    {
        $this->sut->submit('yolo');
        $this->assertFalse($this->sut->isSynchronized());
        $this->assertNull($this->sut->getData());
    }

}
