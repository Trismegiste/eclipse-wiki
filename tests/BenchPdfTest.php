<?php

/*
 * eclipse-wiki
 */

use App\Service\Pdf\ChromiumPdfWriter;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BenchPdfTest extends KernelTestCase
{

    const ITERATION = 10;

    protected Pdf $webkit;
    protected ChromiumPdfWriter $chromium;

    protected function setUp(): void
    {
        $this->webkit = static::getContainer()->get(Pdf::class);
        $this->chromium = static::getContainer()->get(ChromiumPdfWriter::class);
    }

    public function getContent()
    {
        return [
            ['<html><body>YOLO</body></html>']
        ];
    }

    /** @dataProvider getContent */
    public function testWebkit(string $html)
    {
        $stopwatch = microtime(true);
        for ($k = 0; $k < self::ITERATION; $k++) {
            $this->webkit->generateFromHtml($html, 'webkit.pdf', overwrite: true);
        }
        printf("\nWebkit per PDF = %f\n", (microtime(true) - $stopwatch) / self::ITERATION);
    }

    /** @dataProvider getContent */
    public function testChromium(string $html)
    {
        $stopwatch = microtime(true);
        for ($k = 0; $k < self::ITERATION; $k++) {
            $target = tmpfile();
            $pathname = stream_get_meta_data($target)['uri'] . '.html';
            file_put_contents($pathname, $html);
            $this->chromium->write(new SplFileInfo($pathname), new \SplFileInfo('chromium.pdf'));
        }
        printf("\nChromium per PDF = %f\n", (microtime(true) - $stopwatch) / self::ITERATION);
    }

}
