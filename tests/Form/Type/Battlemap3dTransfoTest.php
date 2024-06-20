<?php

/*
 * eclipse-wiki
 */

use App\Form\Type\Battlemap3dTransfo;
use App\Service\Storage;
use PHPUnit\Framework\TestCase;

class Battlemap3dTransfoTest extends TestCase
{

    protected Battlemap3dTransfo $sut;

    protected function setUp(): void
    {
        $this->sut = new Battlemap3dTransfo($this->createStub(Storage::class), '123');
    }

    public function testFailedReversedWhenEmpty()
    {
        $this->expectException(Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->expectExceptionMessage('empty');
        $this->sut->reverseTransform('');
    }

    public function getCorruptJson(): array
    {
        return [
            ['{"a":123}}'],
            ['{"a":123'],
            ["{'a':123}"],
            ['{a:123}']
        ];
    }

    /** @dataProvider getCorruptJson */
    public function testFailedReversedWhenCorrupt(string $content)
    {
        $this->expectException(Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->expectExceptionMessage('not valid');
        $this->sut->reverseTransform($content);
    }

}
