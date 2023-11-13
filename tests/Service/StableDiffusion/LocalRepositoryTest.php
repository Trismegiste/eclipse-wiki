<?php

/*
 * Eclipse Wiki
 */

use App\Service\StableDiffusion\LocalRepository;
use App\Service\StableDiffusion\PictureInfo;
use App\Tests\Service\StableDiffusion\PngReaderTest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocalRepositoryTest extends KernelTestCase
{

    protected LocalRepository $sut;

    protected function setUp(): void
    {
        $this->sut = self::getContainer()->get(LocalRepository::class);
    }

    public function testFolder()
    {
        $this->assertStringContainsString('var', $this->sut->getRootDir());
    }

    public function testNotFound()
    {
        $result = $this->sut->searchPicture('strawberry');
        $this->assertCount(0, $result);
    }

    public function testFound()
    {
        $folder = __DIR__ . '/../../fixtures';
        $src = join_paths($folder, PngReaderTest::fixture);
        $dst = join_paths($this->sut->getRootDir(), PngReaderTest::fixture);
        copy($src, $dst);

        $result = $this->sut->searchPicture('banana');
        $this->assertCount(0, $result);
        $result = $this->sut->searchPicture('strawberry');
        $this->assertCount(1, $result);
        @unlink($dst);

        $this->assertInstanceOf(PictureInfo::class, $result[0]);
        $this->assertEquals(128, $result[0]->width);
        $this->assertEquals('strawberry', $result[0]->prompt);
    }

}
