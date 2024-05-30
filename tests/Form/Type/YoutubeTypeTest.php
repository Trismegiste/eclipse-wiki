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
            ['https://www.youtube.com/watch?v=PQlhC0lEKzw&ab_channel=bestbass42', 'PQlhC0lEKzw'],
            ['https://youtu.be/1BcnhVVQhxA?si=8UskVPhrONs7isxw', '1BcnhVVQhxA'],
        ];
    }

    public function invalidUrl(): array
    {
        return [
            ['yolo'],
            ['https://youu.be/NiNTrKsQ8TU'],
            ['https://youtube.com/watch?k=0123456789-'],
            ['https://www.youtube.com/watch?v=012345~789a'],
            ['https://www.youtube.com/watch?v=PQlhClEKzw&ab_channel=bestbass42'],
            ['https://youtu.be/1BcnhVQhxA?si=8UskVPhrONs7isxw'],
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

    /** @dataProvider invalidUrl */
    public function testInvalid($url)
    {
        $this->sut->submit($url);
        $this->assertFalse($this->sut->isSynchronized());
        $this->assertNull($this->sut->getData());
    }

}
