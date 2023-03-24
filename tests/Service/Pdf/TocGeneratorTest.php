<?php

/*
 * eclipse-wiki
 */

use App\Service\Pdf\TocGenerator;
use PHPUnit\Framework\TestCase;

class TocGeneratorTest extends TestCase
{

    protected TocGenerator $sut;

    protected function setUp(): void
    {
        $this->sut = new TocGenerator();
    }

    public function getPdf()
    {
        return new SplFileInfo(__DIR__ . '/toctest.pdf');
    }

    public function testExtractMetaInfo(): string
    {
        $dump = $this->sut->extractMeta($this->getPdf(), 1, 1, 'Big');
        $this->assertStringContainsString('BigTitle', $dump);
        $this->assertStringContainsString('level = 1', $dump);

        return $dump;
    }

    /** @depends testExtractMetaInfo */
    public function testGenerateToc(string $receipe)
    {
        $toc = $this->sut->generateToc($this->getPdf(), $receipe);
        $this->assertMatchesRegularExpression('#^"BigTitle"\s+1#m', $toc);

        return $toc;
    }

    /** @depends testGenerateToc */
    public function testInjectToc(string $toc)
    {
        $target = new \SplFileInfo('output.pdf');
        $this->sut->injectToc($this->getPdf(), $target, $toc);
        $this->assertTrue($target->isFile());
        unlink($target->getPathname());
    }

}
