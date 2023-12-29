<?php

/*
 * eclipse-wiki
 */

class ChromiumPdfWriterTest extends PHPUnit\Framework\TestCase
{

    protected $sut;
    protected $twig;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Twig\Environment::class);
        $remoteImg = $this->createMock(App\Service\MwImageCache::class);

        $this->sut = new \App\Service\Pdf\ChromiumPdfWriter($this->twig, __DIR__ . '/../../../var/cache/test', $remoteImg);
    }

    public function testWrite()
    {
        $source = new SplFileInfo('source.html');
        $target = new SplFileInfo('target.pdf');

        file_put_contents($source->getPathname(), '<html><body>Yolo</body></html>');
        $this->sut->write($source, $target);
        $this->assertFileExists($target->getPathname());

        unlink($source->getPathname());
        unlink($target->getPathname());
    }

    public function testRenderToPdf()
    {
        $target = new SplFileInfo('target.pdf');
        $this->twig->expects($this->once())
                ->method('render')
                ->willReturn('<html><body><a href="http://link">Yolo</a><img src="http://yolo.jpg"/></body></html>');
        $this->sut->renderToPdf('dummy', [], $target);
        $this->assertFileExists($target->getPathname());

        unlink($target->getPathname());
    }

}
