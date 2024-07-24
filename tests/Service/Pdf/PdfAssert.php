<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Service\Pdf;

use SplFileInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

/**
 * Assertions on PDF
 */
trait PdfAssert
{

    protected function assertPdfContainsString(string $expected, string $filename): void
    {
        $txtDump = new Process(['pdftotext', $filename, '-']);
        $txtDump->mustRun();
        $this->assertStringContainsString($expected, $txtDump->getOutput());
    }

    protected function assertPdf(SplFileInfo $pdf): void
    {
        $stream = fopen($pdf->getPathname(), 'r');
        $magic = fread($stream, 4);
        $this->assertEquals('%PDF', $magic);
        fclose($stream);
    }

    protected function assertResponsePdf(Response $response): void
    {
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

}
