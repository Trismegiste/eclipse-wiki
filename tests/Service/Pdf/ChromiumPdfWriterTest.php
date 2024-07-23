<?php

/*
 * eclipse-wiki
 */

use App\Service\Pdf\ChromiumPdfWriter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

class ChromiumPdfWriterTest extends KernelTestCase
{

    protected ChromiumPdfWriter $sut;

    protected function setUp(): void
    {
        $this->sut = static::getContainer()->get(ChromiumPdfWriter::class);
    }

    protected function assertPdfContainsString(string $expected, string $filename): void
    {
        $txtDump = new Process(['pdftotext', $filename, '-']);
        $txtDump->mustRun();
        $this->assertStringContainsString('Yolo', $txtDump->getOutput());
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

    protected function assertPdf(SplFileInfo $pdf): void
    {
        $stream = fopen($pdf->getPathname(), 'r');
        $magic = fread($stream, 4);
        $this->assertEquals('%PDF', $magic);
        fclose($stream);
    }

}
