<?php

/*
 * eclipse-wiki
 */

use App\Service\Pdf\ChromiumPdfWriter;
use App\Tests\Service\Pdf\PdfAssert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChromiumPdfWriterTest extends KernelTestCase
{

    use PdfAssert;

    protected ChromiumPdfWriter $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(ChromiumPdfWriter::class);
    }

    public function testDomToPdf()
    {
        $target = new SplFileInfo('target.pdf');

        $source = new DOMDocument();
        $source->loadHTML('<html><body>Yolo</body></html>');
        $this->sut->domToPdf($source, $target);
        $this->assertFileExists($target->getPathname());
        $this->assertPdf($target);
        $this->assertPdfContainsString('Yolo', $target->getPathname());

        unlink($target->getPathname());
    }

    public function testTwigToPdf()
    {
        $target = new SplFileInfo('target.pdf');
        $this->sut->renderToPdf('base.pdf.twig', ['vertex' => ['title' => 'Yolo']], $target);
        $this->assertFileExists($target->getPathname());
        $this->assertPdf($target);
        $this->assertPdfContainsString('Yolo', $target->getPathname());

        unlink($target->getPathname());
    }

}
