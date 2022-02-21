<?php

use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
 * eclipse-wiki
 */

class StorageTest extends KernelTestCase
{

    protected $sut;

    protected function setUp(): void
    {
        static::createKernel();
        $this->sut = static::getContainer()->get(Storage::class);
    }

    public function testRootDir()
    {
        $this->assertStringEndsWith('storage/test', $this->sut->getRootDir());
        $this->assertDirectoryExists($this->sut->getRootDir());
    }

    public function testBinaryResponse()
    {
        $path = join_paths($this->sut->getRootDir(), 'essai.txt');
        file_put_contents($path, 'content');
        /** @var BinaryFileResponse $response */
        $response = $this->sut->createResponse('essai.txt');
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        $this->assertEquals('content', $content);
    }

    public function testSearchByTitle()
    {
        $it = $this->sut->searchByTitleContains('EsSai');
        $this->assertCount(1, $it);
    }

    public function testSearchByName()
    {
        $it = $this->sut->searchByName('essa*.txt');
        $this->assertCount(1, $it);
    }

    public function testDelete()
    {
        $this->sut->delete('essai.txt');
        $path = join_paths($this->sut->getRootDir(), 'essai.txt');
        $this->assertFileDoesNotExist($path);
    }

    public function testNotFound()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->sut->createResponse('essai.txt');
    }

}
